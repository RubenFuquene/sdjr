<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * SCRUM-361, Tarea 3: los dos disparadores que mantienen el compromiso de
 * un pack sincronizado con la capacidad real de sus componentes — el
 * aliado bajando stock a mano (avisar, confirmar, aplicar) y un cliente
 * agotando un componente por compra (ajuste silencioso + marca).
 */
class PackageCommitmentSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.create', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
        Role::findOrCreate('user', 'sanctum');
        Permission::findOrCreate('customer.orders.pay', 'sanctum');
    }

    private function ownerWithCommerce(): array
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        return [$user, $commerce];
    }

    private function componentWithPack(Commerce $commerce, CommerceBranch $branch, int $componentStock, int $packCommitted, int $quantityPerPack = 2): array
    {
        $component = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $component->commerceBranches()->attach($branch->id, [
            'quantity_available' => $componentStock,
            'is_published' => true,
        ]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $pack->commerceBranches()->attach($branch->id, [
            'quantity_available' => $packCommitted,
            'is_published' => false,
        ]);
        $pack->packageItems()->attach($component->id, ['quantity' => $quantityPerPack]);

        return [$component, $pack];
    }

    private function pivotFor(int $productId, int $branchId): ProductCommerceBranch
    {
        return ProductCommerceBranch::query()
            ->where('product_id', $productId)
            ->where('commerce_branch_id', $branchId)
            ->firstOrFail();
    }

    // -----------------------------------------------------------------
    // Disparador manual (3.3-3.5)
    // -----------------------------------------------------------------

    public function test_lowering_component_stock_without_confirmation_returns_409_with_affected_packs(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);

        $response = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 4, 'is_published' => true],
            ],
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('errors.stock.affected_packages.0.package_id', $pack->id);
        $response->assertJsonPath('errors.stock.affected_packages.0.current_quantity', 5);
        $response->assertJsonPath('errors.stock.affected_packages.0.adjusted_quantity', 2);
        $response->assertJsonMissingPath('errors.fiscal');

        // Nada se persistió: ni el stock del componente ni el pack.
        $this->assertSame(10, (int) $this->pivotFor($component->id, $branch->id)->quantity_available);
        $this->assertSame(5, (int) $this->pivotFor($pack->id, $branch->id)->quantity_available);
    }

    public function test_confirming_the_adjustment_applies_both_changes_atomically_without_marking_automatic(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);

        $response = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 4, 'is_published' => true],
            ],
            'confirm_changes' => true,
        ]);

        $response->assertOk();

        $this->assertSame(4, (int) $this->pivotFor($component->id, $branch->id)->quantity_available);

        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(2, (int) $packPivot->quantity_available);
        $this->assertNull($packPivot->auto_adjusted_at);
        $this->assertNull($packPivot->auto_adjusted_from);
    }

    public function test_editing_component_stock_without_affecting_any_pack_does_not_require_confirmation(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 2, quantityPerPack: 2);

        // Baja de 10 a 8: el pack sigue soportado (2 packs * 2 = 4 <= 8).
        $response = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 8, 'is_published' => true],
            ],
        ]);

        $response->assertOk();
        $this->assertSame(8, (int) $this->pivotFor($component->id, $branch->id)->quantity_available);
        $this->assertSame(2, (int) $this->pivotFor($pack->id, $branch->id)->quantity_available);
    }

    public function test_confirmed_adjustment_that_drops_pack_to_zero_unpublishes_it(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 3, quantityPerPack: 2);
        $this->pivotFor($pack->id, $branch->id)->update(['is_published' => true]);

        $response = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 0, 'is_published' => false],
            ],
            'confirm_changes' => true,
        ]);

        $response->assertOk();
        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(0, (int) $packPivot->quantity_available);
        $this->assertFalse((bool) $packPivot->is_published);
    }

    public function test_removing_a_branch_assignment_entirely_is_treated_as_a_drop_to_zero_stock(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);

        // commerce_branches presente pero vacio: quita la asignacion por completo.
        $response = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [],
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('errors.stock.affected_packages.0.package_id', $pack->id);
        $response->assertJsonPath('errors.stock.affected_packages.0.adjusted_quantity', 0);
    }

    // -----------------------------------------------------------------
    // Disparador por compra (3.6-3.9)
    // -----------------------------------------------------------------

    private function confirmedOrderFor(User $customer, CommerceBranch $branch, Product $product, int $quantity): Order
    {
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'commerce_branch_id' => $branch->id,
            'total_price' => 1000,
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => 1000 / max(1, $quantity),
        ]);

        return $order;
    }

    public function test_buying_a_component_silently_adjusts_overcommitted_packs_and_marks_the_row(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.pay');
        Sanctum::actingAs($customer);

        $order = $this->confirmedOrderFor($customer, $branch, $component, 8);

        $response = $this->postJson("/api/v1/orders/{$order->id}/transactions", []);
        $response->assertCreated();

        // Componente: 10 - 8 = 2. Pack ya no soporta 5 (necesitaria 10): baja a 1.
        $this->assertSame(2, (int) $this->pivotFor($component->id, $branch->id)->quantity_available);

        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(1, (int) $packPivot->quantity_available);
        $this->assertNotNull($packPivot->auto_adjusted_at);
        $this->assertSame(5, (int) $packPivot->auto_adjusted_from);
    }

    public function test_buying_a_component_does_not_affect_packs_in_other_branches(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        [$componentA, $packA] = $this->componentWithPack($commerce, $branchA, 10, 5, quantityPerPack: 2);
        // Mismo producto tambien vive en la sede B, con su propio pack.
        $componentA->commerceBranches()->attach($branchB->id, ['quantity_available' => 10, 'is_published' => true]);
        $packB = Product::factory()->create(['commerce_id' => $commerce->id, 'product_type' => Constant::PRODUCT_TYPE_PACKAGE]);
        $packB->commerceBranches()->attach($branchB->id, ['quantity_available' => 5, 'is_published' => false]);
        $packB->packageItems()->attach($componentA->id, ['quantity' => 2]);

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.pay');
        Sanctum::actingAs($customer);

        $order = $this->confirmedOrderFor($customer, $branchA, $componentA, 8);
        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertCreated();

        $this->assertSame(1, (int) $this->pivotFor($packA->id, $branchA->id)->quantity_available);
        // La sede B nunca se tocó: ni el stock del componente ahí ni el pack B.
        $this->assertSame(10, (int) $this->pivotFor($componentA->id, $branchB->id)->quantity_available);
        $this->assertSame(5, (int) $this->pivotFor($packB->id, $branchB->id)->quantity_available);
        $this->assertNull($this->pivotFor($packB->id, $branchB->id)->auto_adjusted_at);
    }

    /**
     * SCRUM-361: agotar el compromiso de un pack (o el stock de un
     * componente) por una compra debe despublicarlo automáticamente — misma
     * regla que ya rige para individuales y para el ajuste automático de la
     * Tarea 3.2, aplicada aquí al descuento físico directo
     * (dismissBranchConfirmedStock). Hallazgo real durante pruebas manuales.
     */
    public function test_buying_the_last_pack_unpublishes_it_and_its_exhausted_component(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 2, 1, quantityPerPack: 2);
        $this->pivotFor($pack->id, $branch->id)->update(['is_published' => true]);

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.pay');
        Sanctum::actingAs($customer);

        $order = $this->confirmedOrderFor($customer, $branch, $pack, 1);
        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertCreated();

        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(0, (int) $packPivot->quantity_available);
        $this->assertFalse((bool) $packPivot->is_published);

        $componentPivot = $this->pivotFor($component->id, $branch->id);
        $this->assertSame(0, (int) $componentPivot->quantity_available);
        $this->assertFalse((bool) $componentPivot->is_published);
    }

    /**
     * Tarea 5.4b: vender un pack, por sí solo, no debe producir ningún
     * ajuste automático — su propio compromiso ya se descontó exactamente
     * por la cantidad vendida, en lockstep con sus componentes.
     */
    public function test_buying_a_package_itself_does_not_leave_an_automatic_adjustment_mark(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);
        $this->pivotFor($pack->id, $branch->id)->update(['is_published' => true]);

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.pay');
        Sanctum::actingAs($customer);

        $order = $this->confirmedOrderFor($customer, $branch, $pack, 1);
        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertCreated();

        // Compromiso del pack: 5 - 1 = 4. Componente: 10 - (1*2) = 8.
        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(4, (int) $packPivot->quantity_available);
        $this->assertSame(8, (int) $this->pivotFor($component->id, $branch->id)->quantity_available);

        // La cuenta cierra exacta: nada quedó marcado como ajustado.
        $this->assertNull($packPivot->auto_adjusted_at);
        $this->assertNull($packPivot->auto_adjusted_from);
    }

    // -----------------------------------------------------------------
    // Limpieza de la marca (3.8)
    // -----------------------------------------------------------------

    public function test_editing_the_packs_own_quantity_clears_the_automatic_adjustment_mark(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);
        $this->pivotFor($pack->id, $branch->id)->update([
            'auto_adjusted_at' => now(),
            'auto_adjusted_from' => 5,
        ]);

        $response = $this->putJson('/api/v1/products/'.$pack->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 2, 'is_published' => false],
            ],
        ]);

        $response->assertOk();
        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(2, (int) $packPivot->quantity_available);
        $this->assertNull($packPivot->auto_adjusted_at);
        $this->assertNull($packPivot->auto_adjusted_from);
    }

    public function test_dismiss_endpoint_clears_the_mark_without_changing_quantity(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);
        $this->pivotFor($pack->id, $branch->id)->update([
            'quantity_available' => 2,
            'auto_adjusted_at' => now(),
            'auto_adjusted_from' => 5,
        ]);

        $response = $this->deleteJson("/api/v1/products/{$pack->id}/branches/{$branch->id}/auto-adjustment");

        $response->assertOk();
        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(2, (int) $packPivot->quantity_available);
        $this->assertNull($packPivot->auto_adjusted_at);
        $this->assertNull($packPivot->auto_adjusted_from);
    }

    public function test_dismiss_endpoint_rejects_a_user_who_does_not_own_the_product(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        [$component, $pack] = $this->componentWithPack($commerce, $branch, 10, 5, quantityPerPack: 2);

        $intruder = User::factory()->create();
        $intruder->givePermissionTo('provider.products.update');
        $this->actingAs($intruder, 'sanctum');

        $response = $this->deleteJson("/api/v1/products/{$pack->id}/branches/{$branch->id}/auto-adjustment");

        $response->assertForbidden();
    }
}
