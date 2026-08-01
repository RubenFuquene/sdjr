<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\CommerceBranch;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class NearbySearchService
{
    /**
     * Busca sucursales cercanas a una ubicación.
     */
    public function nearbyBranches(float $lat, float $lng, float $radius, int $perPage = Constant::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return CommerceBranch::query()
            ->nearby($lat, $lng, $radius)
            ->where('status', Constant::STATUS_ACTIVE)
            ->with(['commerce', 'commerceBranchHours', 'commerceBranchPhotos', 'department', 'city', 'neighborhood'])
            ->paginate($perPage);
    }

    /**
     * Busca productos disponibles en sucursales cercanas a una ubicación.
     *
     * SCRUM-277 Fase 1: el stock y la publicación viven por sede en el pivote
     * product_commerce_branch, ya no en products.quantity_available/status
     * global. El filtro de disponibilidad se mueve al join, restringido a la
     * sede evaluada, y descuenta las reservas de órdenes `pending` (mismo
     * criterio que BranchAvailabilityCalculator) — no basta con comparar la
     * columna cruda: un producto con 5 cargadas y 5 reservadas por una orden
     * pendiente mostraría falsamente 5 disponibles a un cliente nuevo.
     *
     * Restringido a product_type=single: los packs quedan fuera del
     * descubrimiento durante la Fase 1 (Opción A — bloqueados hasta que la
     * Fase 2 les dé su propio cálculo de disponibilidad por sede), de forma
     * explícita aquí y no solo como efecto colateral de que nunca lleguen a
     * tener is_published=true.
     */
    public function nearbyProducts(float $lat, float $lng, float $radius, array $filters = [], int $perPage = Constant::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        $now = Carbon::now();
        $query = Product::query()
            ->where('products.status', Constant::STATUS_ACTIVE)
            ->where('products.product_type', Constant::PRODUCT_TYPE_SINGLE)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('products.expires_at')->orWhere('products.expires_at', '>', $now);
            });

        if (isset($filters['category_id'])) {
            $query->where('products.product_category_id', $filters['category_id']);
        }
        if (isset($filters['max_price'])) {
            $query->where('products.discounted_price', '<=', $filters['max_price']);
        }
        if (isset($filters['commerce_id'])) {
            $query->where('products.commerce_id', $filters['commerce_id']);
        }

        $query->join('product_commerce_branch', 'products.id', '=', 'product_commerce_branch.product_id')
            ->join('commerce_branches', 'product_commerce_branch.commerce_branch_id', '=', 'commerce_branches.id')
            ->where('product_commerce_branch.is_published', true)
            ->whereRaw(
                'product_commerce_branch.quantity_available - '.$this->reservedQuantitySubquery().' > 0',
                [Constant::ORDER_STATUS_PENDING]
            )
            ->whereNotNull('commerce_branches.latitude')
            ->whereNotNull('commerce_branches.longitude')
            ->selectRaw('products.*, MIN(6371 * acos(cos(radians(?)) * cos(radians(commerce_branches.latitude)) * cos(radians(commerce_branches.longitude) - radians(?)) + sin(radians(?)) * sin(radians(commerce_branches.latitude)))) AS nearest_branch_distance_km', [$lat, $lng, $lat])
            ->groupBy('products.id')
            ->having('nearest_branch_distance_km', '<=', $radius)
            ->orderBy('nearest_branch_distance_km');

        return $query->with(['photos', 'category', 'commerce', 'commerceBranches' => function ($q) use ($lat, $lng, $radius) {
            // Mismo filtro de disponibilidad que el join principal, para que
            // "nearest_branch" (NearbyProductResource) sea siempre una sede
            // con stock realmente libre y publicada, no cualquier sede dentro
            // del radio.
            $q->wherePivot('is_published', true)
                ->whereRaw(
                    'product_commerce_branch.quantity_available - '.$this->reservedQuantitySubquery().' > 0',
                    [Constant::ORDER_STATUS_PENDING]
                )
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                // SQLite no soporta HAVING en subqueries sin agregación, así que usamos whereRaw
                ->whereRaw('6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))) <= ?', [
                    $lat, $lng, $lat, $radius,
                ])
                // Necesario para que NearbyBranchResource (via CommerceBranchResource)
                // sirva commerce_name y horarios de recogida sin N+1 por producto.
                ->with(['commerce', 'commerceBranchHours', 'department', 'city', 'neighborhood']);
        }])->paginate($perPage);
    }

    /**
     * Subconsulta correlacionada que suma las unidades reservadas por órdenes
     * en un estado dado para el mismo par (product_id, commerce_branch_id)
     * del pivote evaluado en la fila externa. Mismo criterio de reserva que
     * BranchAvailabilityCalculator::reservedQuantitiesByPair() (solo `pending`
     * reserva en vivo — ver esa clase para el porqué), expresado como SQL
     * crudo porque debe vivir dentro del WHERE de una consulta agregada, no
     * como llamada a servicio por fila.
     */
    private function reservedQuantitySubquery(): string
    {
        return '(
            SELECT COALESCE(SUM(order_items.quantity), 0)
            FROM order_items
            INNER JOIN orders ON orders.id = order_items.order_id
            WHERE order_items.product_id = product_commerce_branch.product_id
              AND orders.commerce_branch_id = product_commerce_branch.commerce_branch_id
              AND orders.status = ?
        )';
    }
}
