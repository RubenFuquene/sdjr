<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * SCRUM-227: los 4 endpoints de package-items respondían con mensajes
 * hardcodeados en inglés, sin pasar por el mecanismo de idioma ya
 * establecido en el proyecto (SetLocale + __(), patrón fijado en
 * SCRUM-354). Verifica que ahora sí resuelven por Accept-Language.
 */
class ProductPackageItemsLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.create', 'sanctum');
        Permission::findOrCreate('provider.products.show', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
        Permission::findOrCreate('provider.products.delete', 'sanctum');
    }

    private function actingAsProvider(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo([
            'provider.products.create',
            'provider.products.show',
            'provider.products.update',
            'provider.products.delete',
        ]);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    /**
     * ShowProductRequest/DeleteProductRequest exigen el rol 'admin' (no
     * solo el permiso) para saltarse la verificación de ownership — es lo
     * único que permite llegar al ModelNotFoundException del service con
     * un id inexistente en vez de que el 403 de propiedad lo intercepte
     * antes (comportamiento ya cubierto por otros tests de SCRUM-334).
     */
    private function actingAsAdminRole(): User
    {
        Role::findOrCreate('admin', 'sanctum');
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->givePermissionTo(['provider.products.show', 'provider.products.delete']);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_store_success_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $singleProduct = Product::factory()->create(['commerce_id' => $commerce->id]);
        $singleProduct->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack QA locale',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $singleProduct->currentSalePrice(),
                'quantity_total' => 3,
                'quantity_available' => 3,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 3],
            ],
            'package_items' => [
                ['product_id' => $singleProduct->id, 'quantity' => 1],
            ],
        ];

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $response->assertOk()->assertJsonPath('message', 'Ítems del pack guardados correctamente.');
    }

    public function test_store_success_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $singleProduct = Product::factory()->create(['commerce_id' => $commerce->id]);
        $singleProduct->commerceBranches()->attach($commerceBranch->id, [
            'quantity_available' => 10,
            'is_published' => true,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack QA locale',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $singleProduct->currentSalePrice(),
                'quantity_total' => 3,
                'quantity_available' => 3,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 3],
            ],
            'package_items' => [
                ['product_id' => $singleProduct->id, 'quantity' => 1],
            ],
        ];

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $response->assertOk()->assertJsonPath('message', 'Product package items stored successfully.');
    }

    public function test_get_success_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $package = Product::factory()->create(['commerce_id' => $commerce->id, 'product_type' => Constant::PRODUCT_TYPE_PACKAGE]);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->getJson('/api/v1/products/commerce/package-items/'.$package->id);

        $response->assertOk()->assertJsonPath('message', 'Ítems del pack obtenidos correctamente.');
    }

    public function test_get_success_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $package = Product::factory()->create(['commerce_id' => $commerce->id, 'product_type' => Constant::PRODUCT_TYPE_PACKAGE]);

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->getJson('/api/v1/products/commerce/package-items/'.$package->id);

        $response->assertOk()->assertJsonPath('message', 'Package items fetched successfully.');
    }

    public function test_update_success_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $package = Product::factory()->create(['commerce_id' => $commerce->id, 'product_type' => Constant::PRODUCT_TYPE_PACKAGE]);
        $package->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 2, 'is_published' => false]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 30, 'is_published' => true]);
        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'original_price' => $product->currentSalePrice() * 5,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 2]],
        ];

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);

        $response->assertOk()->assertJsonPath('message', 'Ítems del pack actualizados correctamente.');
    }

    public function test_update_success_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $commerceBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $package = Product::factory()->create(['commerce_id' => $commerce->id, 'product_type' => Constant::PRODUCT_TYPE_PACKAGE]);
        $package->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 2, 'is_published' => false]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($commerceBranch->id, ['quantity_available' => 30, 'is_published' => true]);
        $package->packageItems()->attach($product->id, ['quantity' => 2]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'original_price' => $product->currentSalePrice() * 5,
            ],
            'package_items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'commerce_branches' => [['commerce_branch_id' => $commerceBranch->id, 'quantity_available' => 2]],
        ];

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);

        $response->assertOk()->assertJsonPath('message', 'Product package items updated successfully.');
    }

    public function test_delete_not_found_message_in_spanish_by_default(): void
    {
        $this->actingAsAdminRole();

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->deleteJson('/api/v1/products/commerce/package-items/999999', ['id' => 999999]);

        $response->assertStatus(404)->assertJsonPath('message', 'No se encontró el pack del producto.');
    }

    public function test_delete_not_found_message_in_english_when_requested(): void
    {
        $this->actingAsAdminRole();

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->deleteJson('/api/v1/products/commerce/package-items/999999', ['id' => 999999]);

        $response->assertStatus(404)->assertJsonPath('message', 'Product package not found.');
    }
}
