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
            ->with(['commerce', 'commerceBranchHours', 'commerceBranchPhotos'])
            ->paginate($perPage);
    }

    /**
     * Busca productos disponibles en sucursales cercanas a una ubicación.
     */
    public function nearbyProducts(float $lat, float $lng, float $radius, array $filters = [], int $perPage = Constant::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        $now = Carbon::now();
        $query = Product::query()
            ->where('products.status', Constant::STATUS_ACTIVE)
            ->where('products.quantity_available', '>', 0)
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
            ->whereNotNull('commerce_branches.latitude')
            ->whereNotNull('commerce_branches.longitude')
            ->selectRaw('products.*, MIN(6371 * acos(cos(radians(?)) * cos(radians(commerce_branches.latitude)) * cos(radians(commerce_branches.longitude) - radians(?)) + sin(radians(?)) * sin(radians(commerce_branches.latitude)))) AS nearest_branch_distance_km', [$lat, $lng, $lat])
            ->groupBy('products.id')
            ->having('nearest_branch_distance_km', '<=', $radius)
            ->orderBy('nearest_branch_distance_km');

        return $query->with(['photos', 'category', 'commerceBranches' => function ($q) use ($lat, $lng, $radius) {
            // SQLite no soporta HAVING en subqueries sin agregación, así que usamos whereRaw
            $q->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw('6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))) <= ?', [
                    $lat, $lng, $lat, $radius,
                ]);
        }])->paginate($perPage);
    }
}
