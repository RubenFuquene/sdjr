<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-277 Fase 1, Tarea 6.3: pruebas de feature de los endpoints de
 * asignación de sedes (Store/Update, Tarea 3.1) y de publicación por sede
 * (endpoint dedicado, Tarea 3.2), incluidos los casos de autorización que
 * el checklist owasp.md señala como el riesgo principal de la Tarea 3.
 */
class ProductBranchAssignmentAndPublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.create', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
    }

    private function ownerWithCommerce(): array
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        return [$user, $commerce];
    }

    // ---------------------------------------------------------------
    // Store / Update: ownership de las sedes enviadas
    // ---------------------------------------------------------------

    public function test_store_rejects_a_branch_belonging_to_another_commerce(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $foreignBranch = CommerceBranch::factory()->create();

        $response = $this->postJson('/api/v1/products', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Producto',
                'product_type' => 'single',
                'original_price' => 100,
                'discounted_price' => 80,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $foreignBranch->id, 'quantity_available' => 10],
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('product_commerce_branch', [
            'commerce_branch_id' => $foreignBranch->id,
        ]);
    }

    public function test_update_rejects_a_branch_belonging_to_another_commerce(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $foreignBranch = CommerceBranch::factory()->create();

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $foreignBranch->id, 'quantity_available' => 10],
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $foreignBranch->id,
        ]);
    }

    // ---------------------------------------------------------------
    // Store / Update: no se puede publicar sin stock, ni un pack
    // ---------------------------------------------------------------

    public function test_store_rejects_publishing_a_branch_with_zero_stock(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $response = $this->postJson('/api/v1/products', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Producto',
                'product_type' => 'single',
                'original_price' => 100,
                'discounted_price' => 80,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 0, 'is_published' => true],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['commerce_branches.0.is_published']);
    }

    /**
     * SCRUM-361, Tarea 5.1: la Fase 1 bloqueaba toda publicación de packs
     * (Opción A) — esta fase la levanta. Con componentes suficientes en la
     * sede, publicar un pack ahora se acepta igual que un individual.
     */
    public function test_store_publishes_a_package_with_enough_component_stock(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack',
                'product_type' => 'package',
                // El techo del pack es la suma de los precios de venta
                // vigentes de sus componentes (ticket derivado de SCRUM-361/323).
                'original_price' => $component->currentSalePrice(),
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5, 'is_published' => true],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_commerce_branch', [
            'commerce_branch_id' => $branch->id,
            'quantity_available' => 5,
            'is_published' => true,
        ]);
    }

    public function test_store_rejects_publishing_a_package_without_enough_component_stock(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack',
                'product_type' => 'package',
                'original_price' => 100,
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 2],
            ],
            'commerce_branches' => [
                // 5 unidades / 2 por pack = máximo 2 packs; se piden 3.
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 3, 'is_published' => true],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['commerce_branches.0.quantity_available']);
    }

    // ---------------------------------------------------------------
    // Endpoint dedicado de publicación (PATCH .../branches/{branchId})
    // ---------------------------------------------------------------

    public function test_publish_endpoint_publishes_a_branch_with_stock(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->patchJson("/api/v1/products/{$product->id}/branches/{$branch->id}", [
            'is_published' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branch->id,
            'is_published' => true,
        ]);
    }

    public function test_publish_endpoint_rejects_zero_stock_branch(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 0, 'is_published' => false]);

        $response = $this->patchJson("/api/v1/products/{$product->id}/branches/{$branch->id}", [
            'is_published' => true,
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branch->id,
            'is_published' => false,
        ]);
    }

    public function test_publish_endpoint_rejects_branch_not_assigned_to_product(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        // Nunca se asigna el producto a esta sede.

        $response = $this->patchJson("/api/v1/products/{$product->id}/branches/{$branch->id}", [
            'is_published' => true,
        ]);

        $response->assertUnprocessable();
    }

    public function test_publish_endpoint_rejects_packages(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->patchJson("/api/v1/products/{$pack->id}/branches/{$branch->id}", [
            'is_published' => true,
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $pack->id,
            'commerce_branch_id' => $branch->id,
            'is_published' => false,
        ]);
    }

    public function test_publish_endpoint_rejects_a_user_who_does_not_own_the_product(): void
    {
        [$owner, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $intruder = User::factory()->create();
        $intruder->givePermissionTo('provider.products.update');
        $this->actingAs($intruder, 'sanctum');

        $response = $this->patchJson("/api/v1/products/{$product->id}/branches/{$branch->id}", [
            'is_published' => true,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branch->id,
            'is_published' => false,
        ]);
    }

    public function test_publish_endpoint_unpublishing_one_branch_does_not_affect_another(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($branchA->id, ['quantity_available' => 5, 'is_published' => true]);
        $product->commerceBranches()->attach($branchB->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->patchJson("/api/v1/products/{$product->id}/branches/{$branchA->id}", [
            'is_published' => false,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchA->id,
            'is_published' => false,
        ]);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchB->id,
            'is_published' => true,
        ]);
    }

    // ---------------------------------------------------------------
    // sync(): editar solo una sede no borra ni toca las demás (Riesgo R3)
    // ---------------------------------------------------------------

    /**
     * commerce_branches representa el estado deseado COMPLETO cuando la clave
     * está presente, no un delta: si el payload solo incluye la sede B, la
     * sede A se suelta, aunque el producto tuviera datos válidos ahí. Esto es
     * deliberado (ver storeCommerceBranches(), Parte 1) — el llamador único
     * conocido (el formulario de asignación multi-sede) siempre envía el
     * arreglo completo de sedes marcadas, nunca un subconjunto parcial. La
     * protección real contra el Riesgo R3 (edición que borra sedes sin
     * querer) es la clave AUSENTE, cubierta en el siguiente test.
     */
    public function test_commerce_branches_key_replaces_the_full_assignment_not_a_delta(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $product->commerceBranches()->attach($branchA->id, ['quantity_available' => 12, 'is_published' => true]);
        $product->commerceBranches()->attach($branchB->id, ['quantity_available' => 7, 'is_published' => false]);
        $originalPivotAId = ProductCommerceBranch::where('product_id', $product->id)
            ->where('commerce_branch_id', $branchA->id)
            ->value('id');

        // Solo se reenvía B, con una cantidad nueva. A no se menciona.
        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branchB->id, 'quantity_available' => 20, 'is_published' => true],
            ],
        ]);

        $response->assertOk();

        // B se actualizó según lo enviado.
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchB->id,
            'quantity_available' => 20,
            'is_published' => true,
        ]);

        // A fue soltada — commerce_branches representa el estado deseado
        // COMPLETO, no un delta (ver storeCommerceBranches(), Parte 1).
        $this->assertDatabaseMissing('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchA->id,
        ]);
        $this->assertNotNull($originalPivotAId);
    }

    /**
     * Complemento del caso anterior: omitir la clave commerce_branches por
     * completo (no solo omitir una sede dentro del array) sí preserva TODA
     * la asignación intacta — es el patrón de valor efectivo ya establecido
     * para SCRUM-303/306 y SCRUM-335.
     */
    public function test_update_without_commerce_branches_key_preserves_all_branches(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 100,
            'discounted_price' => 80,
        ]);
        $product->commerceBranches()->attach($branchA->id, ['quantity_available' => 12, 'is_published' => true]);
        $product->commerceBranches()->attach($branchB->id, ['quantity_available' => 7, 'is_published' => false]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'title' => 'Solo cambia el titulo'],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchA->id,
            'quantity_available' => 12,
            'is_published' => true,
        ]);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchB->id,
            'quantity_available' => 7,
            'is_published' => false,
        ]);
    }
}
