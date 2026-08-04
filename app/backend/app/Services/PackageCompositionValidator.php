<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Reglas de composición de un pack que dependen de la sede (SCRUM-361,
 * Tarea 4 — SCRUM-323). Vive en un servicio, no en los FormRequest, porque
 * StoreProductRequest y UpdateProductRequest la necesitan idéntica: la
 * diferencia entre ambos es solo cómo resuelven el valor efectivo de sedes
 * y componentes (payload vs. valores ya guardados), no la regla en sí.
 */
class PackageCompositionValidator
{
    public function __construct(
        private readonly PackageAvailabilityCalculator $packageAvailabilityCalculator
    ) {}

    /**
     * Componentes sin inventario asignado en alguna de las sedes del pack.
     * Regla confirmada (SCRUM-323): asignado con stock; publicado no se
     * exige — un aliado puede vender un producto solo dentro de packs.
     *
     * @param  Collection<int, array{index:int, product: Product, quantity:int}>  $packageItems
     * @param  Collection<int, int>  $branchIds  Sedes a las que se asigna el pack.
     * @return Collection<int, array{index:int, product: Product, branchId:int}>
     */
    public function componentsMissingBranchStock(Collection $packageItems, Collection $branchIds): Collection
    {
        $result = collect();

        foreach ($packageItems as $item) {
            $item['product']->loadMissing('commerceBranches');

            foreach ($branchIds as $branchId) {
                $pivot = $item['product']->commerceBranches->firstWhere('id', $branchId)?->pivot;

                if (! $pivot || (int) $pivot->quantity_available <= 0) {
                    $result->push([
                        'index' => $item['index'],
                        'product' => $item['product'],
                        'branchId' => $branchId,
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * Máximo de packs ofrecibles, calculado para cada sede del pack, según
     * la capacidad real de sus componentes en esa misma sede.
     *
     * @param  Collection<int, array{index:int, product: Product, quantity:int}>  $packageItems
     * @param  Collection<int, int>  $branchIds
     * @return Collection<int, int> Máximo indexado por commerce_branch_id.
     */
    public function maxPacksByBranch(Collection $packageItems, Collection $branchIds, ?int $excludePackageId): Collection
    {
        return $branchIds->mapWithKeys(fn (int $branchId) => [
            $branchId => $this->packageAvailabilityCalculator->maxPackageQuantity($packageItems, $branchId, $excludePackageId),
        ]);
    }

    /**
     * Un pack se ofrece en una sola sede — **política de negocio revisable,
     * decidida el 2026-08-03**, no un invariante del dominio. El armado
     * multi-sede confundía a los aliados (la lista de candidatos se reducía
     * a la intersección de varias sedes sin explicación clara); para
     * replicar un pack en otra sede se usa "Duplicar" + cambio de sede.
     *
     * Deliberadamente **no** se simplificó el resto de este servicio ni
     * `PackageAvailabilityCalculator` a una sola sede: ambos siguen
     * operando sobre colecciones de sedes. Si esta política se revierte,
     * basta con dejar de llamar este método — no hace falta rehacer el
     * cálculo de disponibilidad ni el modelo de datos, que ya son
     * correctos para N sedes.
     */
    public function exceedsSingleBranchPolicy(Collection $branchIds): bool
    {
        return $branchIds->unique()->count() > 1;
    }
}
