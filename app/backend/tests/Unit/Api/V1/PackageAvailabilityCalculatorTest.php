<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1;

use App\Constants\Constant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PackageAvailabilityCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PackageAvailabilityCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_for_packaging_without_packs_equals_quantity_available(): void
    {
        $calculator = new PackageAvailabilityCalculator;

        $product = Product::factory()->create([
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $this->assertEquals(10, $calculator->availableForPackaging($product));
    }

    public function test_available_for_packaging_with_one_pack_subtracts_committed_stock(): void
    {
        $calculator = new PackageAvailabilityCalculator;

        $product = Product::factory()->create([
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $pack = Product::factory()->create([
            'commerce_id' => $product->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'quantity_total' => 3,
            'quantity_available' => 3,
        ]);

        $pack->packageItems()->attach($product->id, ['quantity' => 2]);

        // 10 - (3 packs * 2 units each) = 4
        $this->assertEquals(4, $calculator->availableForPackaging($product->fresh()));
    }

    public function test_available_for_packaging_with_multiple_packs_sums_committed_stock(): void
    {
        $calculator = new PackageAvailabilityCalculator;

        $product = Product::factory()->create([
            'quantity_total' => 20,
            'quantity_available' => 20,
        ]);

        $packA = Product::factory()->create([
            'commerce_id' => $product->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'quantity_total' => 3,
            'quantity_available' => 3,
        ]);
        $packA->packageItems()->attach($product->id, ['quantity' => 2]);

        $packB = Product::factory()->create([
            'commerce_id' => $product->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'quantity_total' => 2,
            'quantity_available' => 2,
        ]);
        $packB->packageItems()->attach($product->id, ['quantity' => 1]);

        // 20 - (3*2 + 2*1) = 20 - 8 = 12
        $this->assertEquals(12, $calculator->availableForPackaging($product->fresh()));
    }

    public function test_available_for_packaging_with_exclude_package_id_excludes_own_commitment(): void
    {
        $calculator = new PackageAvailabilityCalculator;

        $product = Product::factory()->create([
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $packA = Product::factory()->create([
            'commerce_id' => $product->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'quantity_total' => 3,
            'quantity_available' => 3,
        ]);
        $packA->packageItems()->attach($product->id, ['quantity' => 2]);

        $packB = Product::factory()->create([
            'commerce_id' => $product->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'quantity_total' => 2,
            'quantity_available' => 2,
        ]);
        $packB->packageItems()->attach($product->id, ['quantity' => 1]);

        // Excluding packA's own commitment: 10 - (2*1) = 8
        $this->assertEquals(
            8,
            $calculator->availableForPackaging($product->fresh(), $packA->id)
        );
    }

    public function test_available_for_packaging_never_returns_negative(): void
    {
        $calculator = new PackageAvailabilityCalculator;

        $product = Product::factory()->create([
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $pack = Product::factory()->create([
            'commerce_id' => $product->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        // 10 packs * 2 units = 20 committed, more than the 10 available
        $pack->packageItems()->attach($product->id, ['quantity' => 2]);

        $this->assertEquals(0, $calculator->availableForPackaging($product->fresh()));
    }

    public function test_available_for_packaging_considers_active_order_reservations(): void
    {
        $calculator = new PackageAvailabilityCalculator;

        $product = Product::factory()->create([
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $order = Order::factory()->create(['status' => Constant::ORDER_STATUS_PENDING]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // quantity_available accessor: 10 - 3 reserved = 7, no packs committed
        $this->assertEquals(7, $calculator->availableForPackaging($product->fresh()));
    }

    public function test_max_package_quantity_with_multiple_items_is_governed_by_the_minimum(): void
    {
        $calculator = new PackageAvailabilityCalculator;

        $productA = Product::factory()->create([
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);
        $productB = Product::factory()->create([
            'commerce_id' => $productA->commerce_id,
            'quantity_total' => 9,
            'quantity_available' => 9,
        ]);

        $packageItems = new Collection([
            ['product' => $productA, 'quantity' => 2], // 10 / 2 = 5 possible packs
            ['product' => $productB, 'quantity' => 2], // 9 / 2 = 4 possible packs
        ]);

        $this->assertEquals(4, $calculator->maxPackageQuantity($packageItems));
    }
}