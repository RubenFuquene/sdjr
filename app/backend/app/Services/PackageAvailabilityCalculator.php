<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Servicio de disponibilidad de packs, resuelto por sede (SCRUM-361, Fase 2
 * de SCRUM-277). El compromiso de un pack vive en su propio pivote
 * producto-sede (product_commerce_branch.quantity_available): cuánto stock
 * de un componente queda libre para packs, y cuántos packs se pueden
 * ofrecer, se calculan siempre para una sede concreta — un pack en la sede
 * B nunca consume stock de un componente en la sede A.
 */
class PackageAvailabilityCalculator
{
    public function __construct(
        private readonly BranchAvailabilityCalculator $branchAvailabilityCalculator
    ) {}

    /**
     * Stock de un componente que queda libre para comprometer en packs, en
     * una sede concreta.
     *
     * @param  Product  $component  Un producto con product_type "single".
     * @param  int  $branchId  La sede en la que se evalúa el compromiso.
     * @param  int|null  $excludePackageId  Excluir el compromiso propio de este pack (al editarlo).
     */
    public function availableForPackaging(Product $component, int $branchId, ?int $excludePackageId = null): int
    {
        return $this->availableForPackagingMany(collect([$component]), $branchId, $excludePackageId)
            ->get($component->id, 0);
    }

    /**
     * Igual que availableForPackaging(), pero para N componentes en una
     * sola sede con un número fijo de consultas — nunca una por componente
     * (Tarea 2.4).
     *
     * @param  Collection<int, Product>  $components
     * @return Collection<int, int> Disponibilidad indexada por product_id del componente.
     */
    public function availableForPackagingMany(Collection $components, int $branchId, ?int $excludePackageId = null): Collection
    {
        if ($components->isEmpty()) {
            return collect();
        }

        // loadMissing() sobre una Eloquent Collection resuelve la relación
        // faltante de todos los componentes en una sola consulta — los
        // objetos se comparten por referencia, así que también queda
        // resuelta para $components aunque no sea una Eloquent Collection.
        EloquentCollection::make($components->all())->loadMissing('commerceBranches');
        $componentIds = $components->pluck('id');

        // Todos los packs que incluyen al menos uno de estos componentes y
        // están asignados a esta sede, con sus package_items y su pivote de
        // sede precargados — una sola consulta, no una por componente.
        $packagesAtBranch = Product::query()
            ->where('product_type', Constant::PRODUCT_TYPE_PACKAGE)
            // Sin calificar "id": dentro del closure de whereHas, Eloquent
            // alía la tabla relacionada (laravel_reserved_0) — calificarla
            // como "products.id" apuntaría a la tabla externa (el pack) en
            // vez de a sus componentes, y el filtro nunca encontraría nada.
            ->whereHas('packageItems', fn ($q) => $q->whereIn('id', $componentIds))
            ->when($excludePackageId !== null, fn ($q) => $q->where('id', '!=', $excludePackageId))
            ->with([
                'packageItems' => fn ($q) => $q->whereIn('products.id', $componentIds),
                'commerceBranches' => fn ($q) => $q->wherePivot('commerce_branch_id', $branchId),
            ])
            ->get()
            ->filter(fn (Product $package) => $package->commerceBranches->isNotEmpty());

        $reservedQuantities = $this->reservedQuantitiesByProductId($packagesAtBranch->pluck('id'), $branchId);

        $committedByComponentId = [];

        foreach ($packagesAtBranch as $package) {
            $branchPivot = $package->commerceBranches->first()->pivot;
            $effectiveCommitted = max(0, (int) $branchPivot->quantity_available - $reservedQuantities->get($package->id, 0));

            foreach ($package->packageItems as $item) {
                $committedByComponentId[$item->id] = ($committedByComponentId[$item->id] ?? 0)
                    + $effectiveCommitted * (int) $item->pivot->quantity;
            }
        }

        // Disponibilidad física de los componentes en la sede, también en
        // lote (BranchAvailabilityCalculator::availableForMany ya lo hace).
        $componentPivotsByComponentId = $components->mapWithKeys(
            fn (Product $component) => [$component->id => $component->commerceBranches->firstWhere('id', $branchId)?->pivot]
        )->filter();

        $branchStockByPivotId = $this->branchAvailabilityCalculator->availableForMany($componentPivotsByComponentId->values());

        return $components->mapWithKeys(function (Product $component) use ($componentPivotsByComponentId, $branchStockByPivotId, $committedByComponentId) {
            $pivot = $componentPivotsByComponentId->get($component->id);
            $branchStock = $pivot ? $branchStockByPivotId->get($pivot->id, 0) : 0;
            $committed = $committedByComponentId[$component->id] ?? 0;

            return [$component->id => max(0, $branchStock - $committed)];
        });
    }

    /**
     * Máximo de packs que se pueden ofrecer en una sede concreta, dado el
     * stock disponible de cada componente en esa misma sede.
     *
     * @param  Collection<int, array{product: Product, quantity: int}>  $packageItems  Cada elemento pareja un componente con la cantidad requerida por pack.
     * @param  int  $branchId  La sede en la que se evalúa la capacidad.
     * @param  int|null  $excludePackageId  Excluir el compromiso propio de este pack (al editarlo).
     */
    public function maxPackageQuantity(Collection $packageItems, int $branchId, ?int $excludePackageId = null): int
    {
        if ($packageItems->isEmpty()) {
            return 0;
        }

        $availabilityByComponentId = $this->availableForPackagingMany(
            $packageItems->pluck('product'),
            $branchId,
            $excludePackageId
        );

        return (int) $packageItems
            ->map(function (array $item) use ($availabilityByComponentId) {
                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    return 0;
                }

                return intdiv($availabilityByComponentId->get($item['product']->id, 0), $quantity);
            })
            ->min();
    }

    /**
     * Cantidad reservada por órdenes activas de cada pack, en una sede
     * concreta, con una única consulta agregada. Solo cuenta órdenes en
     * estado `pending` — el resto de estados vivos ya tuvieron su descuento
     * físico aplicado al pasar por `confirmed` (dismissProductConfirmedStock),
     * así que contarlos aquí los restaría dos veces (mismo criterio que
     * BranchAvailabilityCalculator).
     *
     * @param  Collection<int, int>  $productIds
     * @return Collection<int, int> Cantidad reservada indexada por product_id.
     */
    private function reservedQuantitiesByProductId(Collection $productIds, int $branchId): Collection
    {
        if ($productIds->isEmpty()) {
            return collect();
        }

        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', Constant::ORDER_STATUS_PENDING)
            ->where('orders.commerce_branch_id', $branchId)
            ->whereIn('order_items.product_id', $productIds)
            // SCRUM-366/367: $productIds aquí son ids de PACK, y una línea
            // hija nunca tiene el id del pack como su propio product_id, así
            // que este filtro es defensivo — deja explícito que esta cuenta
            // es solo de líneas padre, igual que BranchAvailabilityCalculator.
            ->whereNull('order_items.parent_package_id')
            ->selectRaw('order_items.product_id as product_id, SUM(order_items.quantity) as total')
            ->groupBy('order_items.product_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->product_id => (int) $row->total]);
    }
}
