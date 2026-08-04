<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\OrderItem;
use App\Models\ProductCommerceBranch;
use Illuminate\Support\Collection;

/**
 * Resuelve la disponibilidad de stock de un producto en una sede concreta:
 * la cantidad cargada en el pivote producto-sede, menos las unidades
 * comprometidas por órdenes activas de esa misma sede.
 *
 * SCRUM-277 Fase 1: el inventario vive por sede en product_commerce_branch,
 * ya no en products. Este servicio reemplaza el cálculo global que hacía
 * Product::getQuantityAvailableAttribute().
 */
class BranchAvailabilityCalculator
{
    /**
     * Disponibilidad de un único pivote producto-sede.
     */
    public function availableFor(ProductCommerceBranch $pivot): int
    {
        return $this->availableForMany(collect([$pivot]))->get($pivot->id, 0);
    }

    /**
     * Disponibilidad de varios pivotes producto-sede en una sola consulta
     * agregada, para evitar una consulta por sede al resolver un listado.
     *
     * @param  Collection<int, ProductCommerceBranch>  $pivots
     * @return Collection<int, int> Disponibilidad indexada por el id del pivote.
     */
    public function availableForMany(Collection $pivots): Collection
    {
        $reserved = $this->reservedQuantitiesByPair($pivots);

        return $pivots->mapWithKeys(fn (ProductCommerceBranch $pivot) => [
            $pivot->id => max(0, $pivot->quantity_available - $reserved->get(
                $this->pairKey($pivot->product_id, $pivot->commerce_branch_id),
                0
            )),
        ]);
    }

    /**
     * Cantidad reservada por órdenes activas para cada par (producto, sede),
     * resuelta con una única consulta agregada vía Order.commerce_branch_id.
     *
     * Solo cuenta órdenes en estado `pending`. El resto de estados vivos
     * (`confirmed`, `preparing`, `ready`) ya tuvieron su descuento físico
     * aplicado sobre quantity_available al pasar por `confirmed`
     * (ProductService::dismissProductConfirmedStock), así que contarlos aquí
     * los restaría dos veces: una orden de 3 unidades en preparación dejaba
     * 4 disponibles de 10 en vez de 7, subvendiendo al cliente.
     *
     * `pending` es el único estado donde el stock está comprometido pero
     * todavía no descontado de la columna.
     *
     * @param  Collection<int, ProductCommerceBranch>  $pivots
     * @return Collection<string, int> Cantidad reservada indexada por "product_id:commerce_branch_id".
     */
    private function reservedQuantitiesByPair(Collection $pivots): Collection
    {
        if ($pivots->isEmpty()) {
            return collect();
        }

        $productIds = $pivots->pluck('product_id')->unique()->values();
        $branchIds = $pivots->pluck('commerce_branch_id')->unique()->values();

        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', Constant::ORDER_STATUS_PENDING)
            ->whereIn('order_items.product_id', $productIds)
            ->whereIn('orders.commerce_branch_id', $branchIds)
            // SCRUM-366/367: las líneas hijas de componente (parent_package_id
            // no nulo) son informativas, no reservan stock propio — ese
            // compromiso ya lo cuenta PackageAvailabilityCalculator sobre el
            // pack. Contarlas aquí también sería reservar la misma compra dos
            // veces contra el mismo componente.
            ->whereNull('order_items.parent_package_id')
            ->selectRaw('order_items.product_id as product_id, orders.commerce_branch_id as commerce_branch_id, SUM(order_items.quantity) as total')
            ->groupBy('order_items.product_id', 'orders.commerce_branch_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $this->pairKey((int) $row->product_id, (int) $row->commerce_branch_id) => (int) $row->total,
            ]);
    }

    private function pairKey(int $productId, int $commerceBranchId): string
    {
        return $productId.':'.$commerceBranchId;
    }
}
