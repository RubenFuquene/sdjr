<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Contracts\PaymentGatewayInterface;
use App\Enums\TransactionStatus;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Payments\PaymentIntent;
use App\Payments\PaymentResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderTransactionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'user', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'customer.orders.pay', 'guard_name' => 'sanctum']);
    }

    private function customer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('customer.orders.pay');
        Sanctum::actingAs($user);

        return $user;
    }

    private function pendingOrderFor(User $user, float $total = 16000): Order
    {
        $branch = CommerceBranch::factory()->create();
        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'commerce_branch_id' => $branch->id,
            'total_price' => $total,
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $total / 2,
        ]);

        return $order;
    }

    public function test_owner_pays_pending_order_and_it_gets_confirmed(): void
    {
        $user = $this->customer();
        $order = $this->pendingOrderFor($user);
        $product = $order->items()->first()->product;
        $stockBefore = $product->quantity_total;

        $response = $this->postJson("/api/v1/orders/{$order->id}/transactions", []);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'approved');
        $response->assertJsonPath('data.amount', 16000);
        $response->assertJsonPath('data.currency', 'COP');

        $this->assertSame(Constant::ORDER_STATUS_CONFIRMED, $order->fresh()->status);
        // El stock se descuenta UNA vez, via OrderService (comportamiento existente sobre quantity_total).
        $this->assertSame($stockBefore - 2, $product->fresh()->quantity_total);
    }

    public function test_simulated_rejection_leaves_order_pending(): void
    {
        $user = $this->customer();
        $order = $this->pendingOrderFor($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/transactions", ['simulate' => 'reject']);

        $response->assertStatus(402);
        $response->assertJsonPath('data.status', 'rejected');
        $response->assertJsonPath('status', false);
        $this->assertNotNull($response->json('data.failure_reason'));

        $this->assertSame(Constant::ORDER_STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(1, Transaction::where('order_id', $order->id)->count());
    }

    public function test_rejected_payment_can_be_retried_and_approved(): void
    {
        $user = $this->customer();
        $order = $this->pendingOrderFor($user);

        $this->postJson("/api/v1/orders/{$order->id}/transactions", ['simulate' => 'reject'])->assertStatus(402);
        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertCreated();

        $this->assertSame(Constant::ORDER_STATUS_CONFIRMED, $order->fresh()->status);
        $this->assertSame(2, Transaction::where('order_id', $order->id)->count());
    }

    public function test_paid_order_rejects_second_charge(): void
    {
        $user = $this->customer();
        $order = $this->pendingOrderFor($user);

        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertCreated();
        $response = $this->postJson("/api/v1/orders/{$order->id}/transactions", []);

        $response->assertUnprocessable();
        // Sin segundo cargo: sigue habiendo una sola transaccion approved.
        $this->assertSame(
            1,
            Transaction::where('order_id', $order->id)
                ->where('status', TransactionStatus::Approved->value)
                ->count()
        );
    }

    public function test_non_owner_cannot_pay_the_order(): void
    {
        $owner = User::factory()->create();
        $order = $this->pendingOrderFor($owner);

        // customer() autentica a OTRO usuario con el permiso correcto.
        $this->customer();

        $response = $this->postJson("/api/v1/orders/{$order->id}/transactions", []);

        $response->assertForbidden();
        $this->assertSame(0, Transaction::where('order_id', $order->id)->count());
    }

    public function test_nonexistent_order_returns_forbidden_without_leaking_existence(): void
    {
        $this->customer();

        // 403 tanto para orden ajena como inexistente: no se filtra si el id existe.
        $this->postJson('/api/v1/orders/999999/transactions', [])->assertForbidden();
    }

    public function test_user_without_pay_permission_gets_forbidden(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // sin customer.orders.pay
        Sanctum::actingAs($user);
        $order = $this->pendingOrderFor($user);

        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertForbidden();
    }

    public function test_transaction_response_does_not_expose_raw_gateway_payload(): void
    {
        $user = $this->customer();
        $order = $this->pendingOrderFor($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/transactions", []);

        $response->assertCreated();
        $this->assertArrayNotHasKey('payload', $response->json('data'));
        // Pero el payload SI queda persistido para trazabilidad interna.
        $this->assertNotEmpty(Transaction::where('order_id', $order->id)->first()->payload);
    }

    public function test_paying_two_orders_for_the_same_product_decrements_stock_correctly(): void
    {
        // Regresion de un bug de concurrencia real: dismissProductConfirmedStock
        // leia y escribia Product sin lockForUpdate, asi que dos confirmaciones
        // sobre el mismo producto podian pisarse el descuento (lost update).
        // Sqlite :memory: (el motor de esta suite) no permite simular la
        // condicion de carrera real entre conexiones concurrentes; esta prueba
        // solo cubre que el delta se aplica correctamente orden a orden, no que
        // el lock resuelva la carrera en si (eso depende del motor real en prod).
        $user = $this->customer();
        $branch = CommerceBranch::factory()->create();
        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'quantity_total' => 10,
            'quantity_available' => 10,
        ]);

        $orderA = Order::factory()->create([
            'user_id' => $user->id,
            'commerce_branch_id' => $branch->id,
            'total_price' => 6000,
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);
        OrderItem::factory()->create([
            'order_id' => $orderA->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 3000,
        ]);

        $orderB = Order::factory()->create([
            'user_id' => $user->id,
            'commerce_branch_id' => $branch->id,
            'total_price' => 9000,
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);
        OrderItem::factory()->create([
            'order_id' => $orderB->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 3000,
        ]);

        $this->postJson("/api/v1/orders/{$orderA->id}/transactions", [])->assertCreated();
        $this->postJson("/api/v1/orders/{$orderB->id}/transactions", [])->assertCreated();

        $this->assertSame(5, $product->fresh()->quantity_total);
        $this->assertSame(5, $product->fresh()->quantity_available);
    }

    public function test_gateway_is_swappable_via_config_without_touching_domain(): void
    {
        // Gateway alterno inline: siempre rechaza con una firma reconocible.
        $alternative = new class implements PaymentGatewayInterface
        {
            public function authorize(PaymentIntent $intent): PaymentResult
            {
                return new PaymentResult(
                    status: TransactionStatus::Rejected,
                    externalId: 'alt_gateway_ref',
                    failureReason: 'Rejected by alternative gateway',
                );
            }

            public function status(string $providerReference): PaymentResult
            {
                return new PaymentResult(status: TransactionStatus::Rejected, externalId: $providerReference);
            }
        };

        // Swap por contenedor: mismo mecanismo que usaria una pasarela real
        // registrada en config/payments.php. Ningun archivo de dominio cambia.
        $this->app->singleton(PaymentGatewayInterface::class, fn () => $alternative);

        $user = $this->customer();
        $order = $this->pendingOrderFor($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/transactions", []);

        $response->assertStatus(402);
        $response->assertJsonPath('data.external_id', 'alt_gateway_ref');
        $this->assertSame(Constant::ORDER_STATUS_PENDING, $order->fresh()->status);
    }
}
