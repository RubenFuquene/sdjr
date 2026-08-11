<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use Illuminate\Support\Collection;

/**
 * Mantiene el compromiso de los packs sincronizado con la capacidad real de
 * sus componentes (SCRUM-361, Tarea 3). El compromiso puede quedar
 * sobre-comprometido por dos vías, cada una con un tratamiento distinto
 * porque en una hay un humano en el bucle y en la otra no:
 *
 *   - El aliado baja el stock de un componente (o le quita una sede):
 *     avisar antes, confirmar, y solo entonces aplicar (ver
 *     resolveComponentEditImpact() / applyComponentEditAdjustments() — el
 *     caller, ProductService::update(), decide cuándo exigir confirmación
 *     combinando esto con otros impactos de la misma edición, SCRUM-362).
 *   - Un cliente compra el componente y el pago se confirma: no hay a quién
 *     preguntar, así que se ajusta en silencio y se marca la fila (ver
 *     syncAfterPurchase()).
 *
 * Ambos disparadores comparten el mismo cálculo de impacto
 * (affectedPackagesForComponent()) — la diferencia entre "avisar" y
 * "aplicar" vive solo en cómo cada disparador consume ese resultado.
 */
class PackageCommitmentSyncService
{
    public function __construct(
        private readonly PackageAvailabilityCalculator $packageAvailabilityCalculator
    ) {}

    /**
     * Disparador manual: el aliado edita el stock/sedes de un componente.
     * Detección pura — no aplica nada ni lanza excepción. El caller (hoy,
     * ProductService::update()) decide si exige confirmación combinándolo
     * con otros impactos de la misma edición (SCRUM-362/361, unificación).
     *
     * @param  Collection<int, int>  $branchIds  Sedes donde el stock del componente pudo haber bajado o donde se le quitó la asignación.
     * @return Collection<int, array{package: Product, pivot: ProductCommerceBranch, currentQuantity: int, adjustedQuantity: int}>
     */
    public function resolveComponentEditImpact(Product $component, Collection $branchIds): Collection
    {
        return $branchIds->flatMap(
            fn (int $branchId) => $this->affectedPackagesForComponent($component, $branchId)
        );
    }

    /**
     * Aplica el ajuste ya detectado por resolveComponentEditImpact() y
     * confirmado por el aliado. Sin marcar auto_adjusted_at: el aliado ya
     * vio el detalle y lo autorizó, marcarlo sería avisarle de algo que
     * acaba de aprobar.
     *
     * @param  Collection<int, array{package: Product, pivot: ProductCommerceBranch, currentQuantity: int, adjustedQuantity: int}>  $affected
     */
    public function applyComponentEditAdjustments(Collection $affected): void
    {
        foreach ($affected as $entry) {
            $this->applyAdjustment($entry, markAsAutomatic: false);
        }
    }

    /**
     * @param  Collection<int, array{package: Product, pivot: ProductCommerceBranch, currentQuantity: int, adjustedQuantity: int}>  $affected
     * @return array<int, array{package_id:int, package_title:string, commerce_branch_id:int, current_quantity:int, adjusted_quantity:int}>
     */
    public function toStockPayload(Collection $affected): array
    {
        return $affected->map(fn (array $entry) => $this->toPayload($entry))->values()->all();
    }

    /**
     * Disparador por compra: el pago de una orden acaba de descontar stock
     * de este componente en esta sede. No hay aliado a quien preguntar, así
     * que el ajuste se aplica en silencio y la fila queda marcada
     * (auto_adjusted_at / auto_adjusted_from) para que el panel lo muestre.
     */
    public function syncAfterPurchase(Product $component, int $branchId): void
    {
        foreach ($this->affectedPackagesForComponent($component, $branchId) as $entry) {
            $this->applyAdjustment($entry, markAsAutomatic: true);
        }
    }

    /**
     * Limpia la marca de ajuste automático de un pack en una sede — al
     * editar su cantidad (storeCommerceBranches ya lo hace explícitamente)
     * o al descartar el aviso desde la tarjeta sin cambiar nada (Tarea 3.8).
     */
    public function clearAutomaticAdjustmentMark(ProductCommerceBranch $pivot): void
    {
        $pivot->auto_adjusted_at = null;
        $pivot->auto_adjusted_from = null;
        $pivot->save();
    }

    /**
     * Packs de una sede que, dada la disponibilidad actual de sus
     * componentes, quedan comprometidos por encima de lo que soportan.
     * Único punto de cálculo de impacto — lo consumen los dos disparadores
     * y, indirectamente, la previsualización del 409.
     *
     * @return Collection<int, array{package: Product, pivot: ProductCommerceBranch, currentQuantity: int, adjustedQuantity: int}>
     */
    private function affectedPackagesForComponent(Product $component, int $branchId): Collection
    {
        $packagesAtBranch = Product::query()
            ->where('product_type', Constant::PRODUCT_TYPE_PACKAGE)
            // Sin calificar "id": ver la misma nota en PackageAvailabilityCalculator
            // sobre el alias interno de Eloquent dentro de whereHas().
            ->whereHas('packageItems', fn ($q) => $q->whereIn('id', [$component->id]))
            // wherePivot() no funciona dentro de un closure de whereHas (el
            // builder ahí es el de la EXISTS subquery, no la relación
            // BelongsToMany): Eloquent lo interpreta como un where dinámico
            // literal sobre una columna llamada "pivot" y el filtro nunca
            // empareja nada. Hay que calificar la columna de la tabla pivote
            // directamente — el join a ella ya está implícito en la subquery.
            ->whereHas('commerceBranches', fn ($q) => $q->where('product_commerce_branch.commerce_branch_id', $branchId))
            ->with([
                'packageItems',
                'commerceBranches' => fn ($q) => $q->wherePivot('commerce_branch_id', $branchId)->lockForUpdate(),
            ])
            ->lockForUpdate()
            ->get();

        return $packagesAtBranch
            ->map(function (Product $package) use ($branchId) {
                $pivot = $package->commerceBranches->first()->pivot;
                $currentQuantity = (int) $pivot->quantity_available;

                $packageItems = $package->packageItems->map(fn (Product $item) => [
                    'product' => $item,
                    'quantity' => (int) $item->pivot->quantity,
                ]);

                $maxPacks = $this->packageAvailabilityCalculator->maxPackageQuantity($packageItems, $branchId, $package->id);

                return [
                    'package' => $package,
                    'pivot' => $pivot,
                    'currentQuantity' => $currentQuantity,
                    'adjustedQuantity' => min($currentQuantity, $maxPacks),
                ];
            })
            ->filter(fn (array $entry) => $entry['adjustedQuantity'] < $entry['currentQuantity'])
            ->values();
    }

    /**
     * @param  array{package: Product, pivot: ProductCommerceBranch, currentQuantity: int, adjustedQuantity: int}  $entry
     */
    private function applyAdjustment(array $entry, bool $markAsAutomatic): void
    {
        $pivot = $entry['pivot'];
        $pivot->quantity_available = $entry['adjustedQuantity'];

        if ($markAsAutomatic) {
            $pivot->auto_adjusted_at = now();
            $pivot->auto_adjusted_from = $entry['currentQuantity'];
        }

        // Misma regla que rige para individuales: no se publica sin inventario
        // (aquí, sin compromiso). Venga el ajuste del disparador que venga.
        if ($entry['adjustedQuantity'] === 0) {
            $pivot->is_published = false;
        }

        $pivot->save();
    }

    /**
     * @param  array{package: Product, pivot: ProductCommerceBranch, currentQuantity: int, adjustedQuantity: int}  $entry
     * @return array{package_id:int, package_title:string, commerce_branch_id:int, current_quantity:int, adjusted_quantity:int}
     */
    private function toPayload(array $entry): array
    {
        return [
            'package_id' => $entry['package']->id,
            'package_title' => $entry['package']->title,
            'commerce_branch_id' => $entry['pivot']->commerce_branch_id,
            'current_quantity' => $entry['currentQuantity'],
            'adjusted_quantity' => $entry['adjustedQuantity'],
        ];
    }
}
