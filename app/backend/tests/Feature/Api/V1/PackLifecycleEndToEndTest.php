<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * SCRUM-361, Tarea 7.7 (actualizado 2026-08-03 tras el ajuste "un pack, una
 * sede"): recorrido de extremo a extremo — crear un pack en una sede,
 * publicarlo, verificar visibilidad en el descubrimiento, comprar,
 * "duplicarlo" a otra sede (dos altas independientes, que es como funciona
 * duplicar a nivel de API — no hay endpoint de copia), y verificar que
 * ambos packs quedan completamente independientes. Cada paso reutiliza los
 * endpoints reales, no llamadas directas a servicios.
 */
class PackLifecycleEndToEndTest extends TestCase
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

    private function pivotFor(int $productId, int $branchId): ProductCommerceBranch
    {
        return ProductCommerceBranch::query()
            ->where('product_id', $productId)
            ->where('commerce_branch_id', $branchId)
            ->firstOrFail();
    }

    public function test_full_pack_lifecycle_across_branches(): void
    {
        $owner = User::factory()->create();
        $owner->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $owner->id]);
        $category = ProductCategory::factory()->create();
        $this->actingAs($owner, 'sanctum');

        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id, 'latitude' => 10, 'longitude' => 10]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id, 'latitude' => 10, 'longitude' => 10]);

        // 1. Componente con stock en ambas sedes (un individual sí puede
        // vivir en varias). Precio y descuento explícitos: el factory los
        // genera aleatorios/nulos, y el paso 7 reedita este producto —
        // UpdateProductRequest exige discounted_price para individuales,
        // resuelto por valor efectivo desde BD.
        $component = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $component->commerceBranches()->attach($branchA->id, ['quantity_available' => 10, 'is_published' => true]);
        $component->commerceBranches()->attach($branchB->id, ['quantity_available' => 10, 'is_published' => true]);

        // 2. Crear el pack en UNA sola sede (política 2026-08-03). status=1
        // explícito: la columna por defecto en BD es inactivo (borrador)
        // hasta que el aliado lo active, igual que cualquier producto nuevo.
        $createResponse = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack E2E',
                'product_type' => 'package',
                // Techo del pack = precio con descuento del componente (80) * 2.
                'original_price' => 160,
                'status' => (string) Constant::STATUS_ACTIVE,
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 2],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branchA->id, 'quantity_available' => 3, 'is_published' => false],
            ],
        ]);
        $createResponse->assertOk();
        $packAId = $createResponse->json('data.id');

        // 3. Publicar en su única sede.
        $publishResponse = $this->patchJson("/api/v1/products/{$packAId}/branches/{$branchA->id}", [
            'is_published' => true,
        ]);
        $publishResponse->assertOk();

        // 4. Aparece en el descubrimiento en la sede A.
        $discoverResponse = $this->getJson('/api/v1/nearby/products?latitude=10&longitude=10&radius=10');
        $discoverResponse->assertOk();
        $discoverResponse->assertJsonFragment(['id' => (int) $packAId]);
        $packEntry = collect($discoverResponse->json('data'))->firstWhere('id', (int) $packAId);
        $this->assertSame($branchA->id, $packEntry['nearest_branch']['id']);

        // 5. Un cliente compra 1 pack en la sede A: el compromiso baja y el
        // componente se descuenta ahí, la sede B (donde el componente
        // también vive, pero ningún pack todavía) queda intacta.
        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.pay');
        Sanctum::actingAs($customer);

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'commerce_branch_id' => $branchA->id,
            'total_price' => 1000,
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $packAId,
            'quantity' => 1,
            'unit_price' => 1000,
        ]);
        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertCreated();

        $this->assertSame(2, (int) $this->pivotFor((int) $packAId, $branchA->id)->quantity_available);
        $this->assertSame(8, (int) $this->pivotFor($component->id, $branchA->id)->quantity_available);
        $this->assertSame(10, (int) $this->pivotFor($component->id, $branchB->id)->quantity_available);

        // 6. "Duplicar" el pack a la sede B. A nivel de API es una segunda
        // alta independiente (no hay endpoint de copia — duplicar es
        // hidratar el formulario de creación con los datos del pack
        // existente, comportamiento de frontend). El componente también
        // tiene stock en B, así que la reconciliación lo conserva.
        Sanctum::actingAs($owner);
        $duplicateResponse = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack E2E (copia)',
                'product_type' => 'package',
                // Techo del pack = precio con descuento del componente (80) * 2.
                'original_price' => 160,
                'status' => (string) Constant::STATUS_ACTIVE,
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 2],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branchB->id, 'quantity_available' => 3, 'is_published' => true],
            ],
        ]);
        $duplicateResponse->assertOk();
        $packBId = $duplicateResponse->json('data.id');
        $this->assertNotSame($packAId, $packBId);

        // Los dos packs son completamente independientes: comprar en A no
        // tocó ni el compromiso ni el componente del lado de B.
        $this->assertSame(3, (int) $this->pivotFor((int) $packBId, $branchB->id)->quantity_available);
        $this->assertSame(10, (int) $this->pivotFor($component->id, $branchB->id)->quantity_available);

        // 7. El aliado baja el stock del componente en la sede A a un punto
        // que deja el pack A sobre-comprometido (2 packs * 2 = 4 necesarios,
        // solo quedarían 3): la API exige confirmación. La sede B no se toca
        // en este payload, así que el pack B no debería figurar afectado.
        Sanctum::actingAs($owner);
        $lowerStockResponse = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branchA->id, 'quantity_available' => 3, 'is_published' => true],
                ['commerce_branch_id' => $branchB->id, 'quantity_available' => 10, 'is_published' => true],
            ],
        ]);
        $lowerStockResponse->assertStatus(409);
        $affectedPackageIds = collect($lowerStockResponse->json('errors.stock.affected_packages'))->pluck('package_id');
        $this->assertTrue($affectedPackageIds->contains((int) $packAId));
        $this->assertFalse($affectedPackageIds->contains((int) $packBId));

        // 8. Confirma el ajuste: el stock baja y el pack A se recorta a lo
        // que sus componentes soportan (floor(3/2) = 1). El pack B, en su
        // propia sede, permanece intacto.
        $confirmResponse = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branchA->id, 'quantity_available' => 3, 'is_published' => true],
                ['commerce_branch_id' => $branchB->id, 'quantity_available' => 10, 'is_published' => true],
            ],
            'confirm_changes' => true,
        ]);
        $confirmResponse->assertOk();

        $this->assertSame(3, (int) $this->pivotFor($component->id, $branchA->id)->quantity_available);
        $packPivotA = $this->pivotFor((int) $packAId, $branchA->id);
        $this->assertSame(1, (int) $packPivotA->quantity_available);
        // Confirmado por el aliado: no queda marcado como ajuste automático.
        $this->assertNull($packPivotA->auto_adjusted_at);

        $packPivotB = $this->pivotFor((int) $packBId, $branchB->id);
        $this->assertSame(3, (int) $packPivotB->quantity_available);
        $this->assertNull($packPivotB->auto_adjusted_at);
    }

    public function test_creating_a_package_with_more_than_one_branch_is_rejected(): void
    {
        $owner = User::factory()->create();
        $owner->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $owner->id]);
        $category = ProductCategory::factory()->create();
        $this->actingAs($owner, 'sanctum');

        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branchA->id, ['quantity_available' => 10, 'is_published' => true]);
        $component->commerceBranches()->attach($branchB->id, ['quantity_available' => 10, 'is_published' => true]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack multi-sede rechazado',
                'product_type' => 'package',
                'original_price' => 100,
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branchA->id, 'quantity_available' => 1],
                ['commerce_branch_id' => $branchB->id, 'quantity_available' => 1],
            ],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['commerce_branches']);
        $this->assertDatabaseMissing('products', ['title' => 'Pack multi-sede rechazado']);
    }
}
