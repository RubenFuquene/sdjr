<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Service class for calculating package (pack) stock availability.
 *
 * Computes, in real time, how many units of a single product remain
 * available to be committed to packages, and how many packs a given
 * combination of items can support given current stock and existing
 * commitments from other packs.
 */
class PackageAvailabilityCalculator
{
    /**
     * Get the stock of a single product that is still available to be
     * committed to packages, after subtracting the stock already
     * committed by packages that include it.
     *
     * @param  Product  $product  A product with product_type "single".
     * @param  int|null  $excludePackageId  Exclude this package's own commitment from the calculation (used when editing a pack).
     */
    public function availableForPackaging(Product $product, ?int $excludePackageId = null): int
    {
        $product->loadMissing('package');

        $packages = $product->package
            ->when(
                $excludePackageId !== null,
                fn (Collection $packages) => $packages->reject(
                    fn (Product $package) => $package->id === $excludePackageId
                )
            );

        // Resolve the active-order reservations for all involved packages with a single
        // aggregate query, instead of relying on Product::quantity_available's per-model query.
        $reservedQuantities = $this->reservedQuantitiesByProductId($packages->pluck('id'));

        $committedStock = $packages->sum(function (Product $package) use ($reservedQuantities) {
            $effectiveQuantityAvailable = (int) $package->getAttributes()['quantity_available']
                - $reservedQuantities->get($package->id, 0);

            return $effectiveQuantityAvailable * (int) $package->pivot->quantity;
        });

        return max(0, $product->quantity_available - $committedStock);
    }

    /**
     * Get the maximum number of packs that can be offered given the
     * available stock of each component product.
     *
     * @param  Collection<int, array{product: Product, quantity: int}>  $packageItems  Each entry pairs a component product with the quantity required per pack.
     * @param  int|null  $excludePackageId  Exclude this package's own commitment from the calculation (used when editing a pack).
     */
    public function maxPackageQuantity(Collection $packageItems, ?int $excludePackageId = null): int
    {
        if ($packageItems->isEmpty()) {
            return 0;
        }

        return (int) $packageItems
            ->map(function (array $item) use ($excludePackageId) {
                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    return 0;
                }

                return intdiv($this->availableForPackaging($item['product'], $excludePackageId), $quantity);
            })
            ->min();
    }

    /**
     * Get the quantity reserved by active orders for each of the given product IDs,
     * in a single aggregate query.
     *
     * @param  Collection<int, int>  $productIds
     * @return Collection<int, int> Reserved quantity keyed by product ID.
     */
    private function reservedQuantitiesByProductId(Collection $productIds): Collection
    {
        if ($productIds->isEmpty()) {
            return collect();
        }

        return OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', [
                Constant::ORDER_STATUS_PENDING,
                Constant::ORDER_STATUS_PREPARING,
                Constant::ORDER_STATUS_READY,
            ]);
        })
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->product_id => (int) $row->total]);
    }
}