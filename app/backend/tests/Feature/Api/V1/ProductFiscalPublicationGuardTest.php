<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\EstablishmentType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362 (4.1/4.3): un producto con fiscal_code = otro_verificar no se
 * publica en ninguna sede, y no se puede agregar como componente de un pack.
 */
class ProductFiscalPublicationGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.create', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
    }

    private function actingAsProvider(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    private function commerceOfRetail(User $user): Commerce
    {
        $establishmentType = EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_RETAIL]);

        return Commerce::factory()->create([
            'owner_user_id' => $user->id,
            'establishment_type_id' => $establishmentType->id,
        ]);
    }

    public function test_cannot_publish_a_product_pending_review_on_creation(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfRetail($user);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $response = $this->postJson('/api/v1/products', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'fiscal_code' => 'otro_verificar',
                'title' => 'Producto Pendiente',
                'product_type' => Constant::PRODUCT_TYPE_SINGLE,
                'original_price' => 10,
                'discounted_price' => 10,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5, 'is_published' => true],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commerce_branches.0.is_published']);
    }

    public function test_cannot_publish_a_product_pending_review_on_update(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfRetail($user);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5, 'is_published' => true],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commerce_branches.0.is_published']);
    }

    public function test_dedicated_publish_endpoint_rejects_pending_review_product(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfRetail($user);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->patchJson('/api/v1/products/'.$product->id.'/branches/'.$branch->id, [
            'is_published' => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_published']);
    }

    public function test_cannot_add_pending_review_product_as_package_component(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfRetail($user);

        $pendingComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
        ]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Pendiente',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $pendingComponent->currentSalePrice(),
            ],
            'package_items' => [
                ['product_id' => $pendingComponent->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['package_items.0.product_id']);
    }

    public function test_cannot_add_pending_review_product_to_existing_package_on_update(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfRetail($user);

        $validComponent = Product::factory()->create(['commerce_id' => $commerce->id]);
        $pendingComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
        ]);

        $pack = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Editable',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $validComponent->currentSalePrice(),
            ],
            'package_items' => [
                ['product_id' => $validComponent->id, 'quantity' => 1],
            ],
        ])->json('data.id');

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$pack, [
            'product' => ['commerce_id' => $commerce->id, 'original_price' => $pendingComponent->currentSalePrice()],
            'package_items' => [
                ['product_id' => $pendingComponent->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['package_items.0.product_id']);
    }
}
