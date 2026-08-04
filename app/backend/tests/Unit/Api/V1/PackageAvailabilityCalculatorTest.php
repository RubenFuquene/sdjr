<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PackageAvailabilityCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * SCRUM-361 (Fase 2 de SCRUM-277): el compromiso de un pack vive en su
 * propio pivote producto-sede (no en products.quantity_*, eliminadas en
 * esta fase), y toda la disponibilidad se calcula para una sede concreta —
 * un pack en la sede B nunca consume stock de un componente en la sede A.
 */
class PackageAvailabilityCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private function singleProductWithStock(Commerce $commerce, CommerceBranch $branch, int $quantity): Product
    {
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);

        $product->commerceBranches()->attach($branch->id, [
            'quantity_available' => $quantity,
            'is_published' => true,
        ]);

        return $product;
    }

    private function packageCommittedAt(Commerce $commerce, CommerceBranch $branch, int $committed): Product
    {
        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);

        $pack->commerceBranches()->attach($branch->id, [
            'quantity_available' => $committed,
            'is_published' => false,
        ]);

        return $pack;
    }

    public function test_available_for_packaging_without_packs_equals_branch_stock(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = $this->singleProductWithStock($commerce, $branch, 10);

        $this->assertEquals(10, $calculator->availableForPackaging($product, $branch->id));
    }

    public function test_available_for_packaging_with_one_pack_subtracts_committed_stock(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = $this->singleProductWithStock($commerce, $branch, 10);

        $pack = $this->packageCommittedAt($commerce, $branch, 3);
        $pack->packageItems()->attach($product->id, ['quantity' => 2]);

        // 10 - (3 packs * 2 units each) = 4
        $this->assertEquals(4, $calculator->availableForPackaging($product->fresh(), $branch->id));
    }

    public function test_available_for_packaging_with_multiple_packs_sums_committed_stock(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = $this->singleProductWithStock($commerce, $branch, 20);

        $packA = $this->packageCommittedAt($commerce, $branch, 3);
        $packA->packageItems()->attach($product->id, ['quantity' => 2]);

        $packB = $this->packageCommittedAt($commerce, $branch, 2);
        $packB->packageItems()->attach($product->id, ['quantity' => 1]);

        // 20 - (3*2 + 2*1) = 20 - 8 = 12
        $this->assertEquals(12, $calculator->availableForPackaging($product->fresh(), $branch->id));
    }

    public function test_available_for_packaging_with_exclude_package_id_excludes_own_commitment(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = $this->singleProductWithStock($commerce, $branch, 10);

        $packA = $this->packageCommittedAt($commerce, $branch, 3);
        $packA->packageItems()->attach($product->id, ['quantity' => 2]);

        $packB = $this->packageCommittedAt($commerce, $branch, 2);
        $packB->packageItems()->attach($product->id, ['quantity' => 1]);

        // Excluding packA's own commitment: 10 - (2*1) = 8
        $this->assertEquals(
            8,
            $calculator->availableForPackaging($product->fresh(), $branch->id, $packA->id)
        );
    }

    public function test_available_for_packaging_never_returns_negative(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = $this->singleProductWithStock($commerce, $branch, 10);

        $pack = $this->packageCommittedAt($commerce, $branch, 10);
        // 10 packs * 2 units = 20 committed, more than the 10 available
        $pack->packageItems()->attach($product->id, ['quantity' => 2]);

        $this->assertEquals(0, $calculator->availableForPackaging($product->fresh(), $branch->id));
    }

    public function test_available_for_packaging_considers_active_order_reservations(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = $this->singleProductWithStock($commerce, $branch, 10);

        $order = Order::factory()->create([
            'commerce_branch_id' => $branch->id,
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // 10 - 3 reservados por orden pending = 7, sin packs comprometidos
        $this->assertEquals(7, $calculator->availableForPackaging($product->fresh(), $branch->id));
    }

    public function test_available_for_packaging_ignores_packs_committed_in_other_branches(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = $this->singleProductWithStock($commerce, $branchA, 10);
        $product->commerceBranches()->attach($branchB->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $packInA = $this->packageCommittedAt($commerce, $branchA, 3);
        $packInA->packageItems()->attach($product->id, ['quantity' => 2]);

        // La sede A absorbe el compromiso del pack; la sede B queda intacta.
        $this->assertEquals(4, $calculator->availableForPackaging($product->fresh(), $branchA->id));
        $this->assertEquals(10, $calculator->availableForPackaging($product->fresh(), $branchB->id));
    }

    public function test_available_for_packaging_ignores_reservations_in_other_branches(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = $this->singleProductWithStock($commerce, $branchA, 10);

        $pack = $this->packageCommittedAt($commerce, $branchA, 2);
        $pack->packageItems()->attach($product->id, ['quantity' => 1]);

        $orderElsewhere = Order::factory()->create([
            'commerce_branch_id' => $branchB->id,
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);
        OrderItem::factory()->create([
            'order_id' => $orderElsewhere->id,
            'product_id' => $pack->id,
            'quantity' => 1,
        ]);

        // La reserva pending del pack ocurrió en la sede B: no debe restarse
        // del compromiso del pack evaluado en la sede A.
        $this->assertEquals(8, $calculator->availableForPackaging($product->fresh(), $branchA->id));
    }

    public function test_max_package_quantity_with_multiple_items_is_governed_by_the_minimum(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $productA = $this->singleProductWithStock($commerce, $branch, 10);
        $productB = $this->singleProductWithStock($commerce, $branch, 9);

        $packageItems = new Collection([
            ['product' => $productA, 'quantity' => 2], // 10 / 2 = 5 possible packs
            ['product' => $productB, 'quantity' => 2], // 9 / 2 = 4 possible packs
        ]);

        $this->assertEquals(4, $calculator->maxPackageQuantity($packageItems, $branch->id));
    }

    /**
     * Task 2.4: resolver la disponibilidad de N componentes en una sede no
     * debe emitir una consulta por componente — el número de consultas debe
     * mantenerse constante sin importar cuántos componentes tenga el pack.
     */
    public function test_max_package_quantity_does_not_n_plus_one_per_component(): void
    {
        $calculator = app(PackageAvailabilityCalculator::class);

        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $buildPackageItems = fn (int $count) => new Collection(
            collect(range(1, $count))->map(fn () => [
                'product' => $this->singleProductWithStock($commerce, $branch, 10),
                'quantity' => 1,
            ])->all()
        );

        $packageItems5 = $buildPackageItems(5);
        $packageItems10 = $buildPackageItems(10);

        \Illuminate\Support\Facades\DB::enableQueryLog();

        \Illuminate\Support\Facades\DB::flushQueryLog();
        $calculator->maxPackageQuantity($packageItems5, $branch->id);
        $queriesFor5 = count(\Illuminate\Support\Facades\DB::getQueryLog());

        \Illuminate\Support\Facades\DB::flushQueryLog();
        $calculator->maxPackageQuantity($packageItems10, $branch->id);
        $queriesFor10 = count(\Illuminate\Support\Facades\DB::getQueryLog());

        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertEquals(
            $queriesFor5,
            $queriesFor10,
            'Computing maxPackageQuantity should issue the same number of queries regardless of component count (no N+1 across components).'
        );
    }
}
