<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use App\Services\BranchAvailabilityCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * SCRUM-277 Fase 1, Tarea 6.2: pruebas del resolutor de disponibilidad por
 * sede. Cubre el criterio central del refactor — el stock vive por sede y
 * las reservas de una sede nunca contaminan otra — y el bug de doble conteo
 * corregido durante la Tarea 3.6 (ver Parte 1): solo `pending` reserva en
 * vivo, porque `confirmed`/`preparing`/`ready` ya tuvieron su descuento
 * físico aplicado sobre quantity_available.
 */
class BranchAvailabilityCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private function pivotFor(int $quantityAvailable): ProductCommerceBranch
    {
        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);

        return ProductCommerceBranch::create([
            'product_id' => $product->id,
            'commerce_branch_id' => $branch->id,
            'quantity_available' => $quantityAvailable,
            'is_published' => true,
        ]);
    }

    private function reserve(ProductCommerceBranch $pivot, int $quantity, string $status): void
    {
        $order = Order::factory()->create([
            'commerce_branch_id' => $pivot->commerce_branch_id,
            'status' => $status,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $pivot->product_id,
            'quantity' => $quantity,
        ]);
    }

    public function test_available_without_orders_equals_quantity_available(): void
    {
        $pivot = $this->pivotFor(10);

        $this->assertSame(10, app(BranchAvailabilityCalculator::class)->availableFor($pivot));
    }

    public function test_pending_order_reduces_availability(): void
    {
        $pivot = $this->pivotFor(10);
        $this->reserve($pivot, 3, Constant::ORDER_STATUS_PENDING);

        $this->assertSame(7, app(BranchAvailabilityCalculator::class)->availableFor($pivot->fresh()));
    }

    /**
     * Regresión del bug de doble conteo (Tarea 3.6): confirmed/preparing/
     * ready ya tuvieron su descuento físico al pasar por
     * dismissProductConfirmedStock(), así que NO deben restarse de nuevo
     * aquí — de lo contrario un producto con 10 y una orden de 3 en
     * preparación mostraría 7 en vez de 10.
     */
    public function test_confirmed_preparing_ready_and_delivered_orders_do_not_reduce_availability(): void
    {
        $pivot = $this->pivotFor(10);

        foreach ([
            Constant::ORDER_STATUS_CONFIRMED,
            Constant::ORDER_STATUS_PREPARING,
            Constant::ORDER_STATUS_READY,
            Constant::ORDER_STATUS_DELIVERED,
        ] as $status) {
            $this->reserve($pivot, 3, $status);
        }

        $this->assertSame(10, app(BranchAvailabilityCalculator::class)->availableFor($pivot->fresh()));
    }

    public function test_cancelled_order_does_not_reduce_availability(): void
    {
        $pivot = $this->pivotFor(10);
        $this->reserve($pivot, 5, Constant::ORDER_STATUS_CANCELLED);

        $this->assertSame(10, app(BranchAvailabilityCalculator::class)->availableFor($pivot->fresh()));
    }

    public function test_never_returns_negative_when_reservations_exceed_stock(): void
    {
        $pivot = $this->pivotFor(2);
        $this->reserve($pivot, 5, Constant::ORDER_STATUS_PENDING);

        $this->assertSame(0, app(BranchAvailabilityCalculator::class)->availableFor($pivot->fresh()));
    }

    /**
     * El criterio central del refactor: una reserva en la sede A no debe
     * afectar la disponibilidad del mismo producto en la sede B.
     */
    public function test_reservation_in_one_branch_does_not_affect_another_branch_of_the_same_product(): void
    {
        $commerce = Commerce::factory()->create();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);

        $pivotA = ProductCommerceBranch::create([
            'product_id' => $product->id,
            'commerce_branch_id' => $branchA->id,
            'quantity_available' => 10,
            'is_published' => true,
        ]);
        $pivotB = ProductCommerceBranch::create([
            'product_id' => $product->id,
            'commerce_branch_id' => $branchB->id,
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $this->reserve($pivotA, 8, Constant::ORDER_STATUS_PENDING);

        $calculator = app(BranchAvailabilityCalculator::class);
        $this->assertSame(2, $calculator->availableFor($pivotA->fresh()));
        $this->assertSame(10, $calculator->availableFor($pivotB->fresh()));
    }

    /**
     * availableForMany() debe resolver N pivotes con una sola consulta
     * agregada (rendimiento-db.md) y devolver el mismo resultado que
     * availableFor() resuelto individualmente.
     */
    public function test_available_for_many_resolves_multiple_pivots_in_a_single_query(): void
    {
        $pivotOne = $this->pivotFor(10);
        $this->reserve($pivotOne, 4, Constant::ORDER_STATUS_PENDING);

        $pivotTwo = $this->pivotFor(5);

        // Resolver los pivotes ANTES de medir: solo interesa contar las
        // queries que hace availableForMany() en sí, no la preparación del test.
        $pivots = collect([$pivotOne->fresh(), $pivotTwo->fresh()]);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $result = app(BranchAvailabilityCalculator::class)->availableForMany($pivots);
        $queryCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertSame(6, $result->get($pivotOne->id));
        $this->assertSame(5, $result->get($pivotTwo->id));
        $this->assertSame(1, $queryCount, 'Expected a single aggregate query, not one per pivot.');
    }

    public function test_available_for_many_with_empty_collection_returns_empty_without_querying(): void
    {
        \Illuminate\Support\Facades\DB::enableQueryLog();
        $result = app(BranchAvailabilityCalculator::class)->availableForMany(collect());
        $queryCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertTrue($result->isEmpty());
        $this->assertSame(0, $queryCount);
    }
}
