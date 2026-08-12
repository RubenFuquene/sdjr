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
 * SCRUM-362: clasificación fiscal obligatoria por producto individual,
 * nunca digitada por el aliado, y acotada al conjunto que el comercio tiene
 * permitido según FiscalCodeResolver.
 */
class ProductFiscalCodeTest extends TestCase
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

    private function commerceOfType(User $user, string $establishmentTypeCode, bool $operatesUnderFranchise = false): Commerce
    {
        $establishmentType = EstablishmentType::factory()->create(['code' => $establishmentTypeCode]);

        return Commerce::factory()->create([
            'owner_user_id' => $user->id,
            'establishment_type_id' => $establishmentType->id,
            'operates_under_franchise' => $operatesUnderFranchise,
        ]);
    }

    private function baseProductPayload(Commerce $commerce): array
    {
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        return [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Producto Test',
                'product_type' => Constant::PRODUCT_TYPE_SINGLE,
                'original_price' => 100.0,
                'discounted_price' => 90.0,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 10],
            ],
        ];
    }

    public function test_single_product_requires_fiscal_code(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RETAIL);

        $response = $this->postJson('/api/v1/products', $this->baseProductPayload($commerce));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product.fiscal_code']);
    }

    public function test_client_sent_rates_are_ignored_and_derived(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RETAIL);

        $payload = $this->baseProductPayload($commerce);
        $payload['product']['fiscal_code'] = 'iva_19_general';
        $payload['product']['vat_rate'] = 0;
        $payload['product']['applies_inc'] = true;
        $payload['product']['inc_rate'] = 99;

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('products', [
            'title' => 'Producto Test',
            'fiscal_code' => 'iva_19_general',
            'vat_rate' => 19.0,
            'applies_inc' => 0,
            'inc_rate' => 0.0,
        ]);
    }

    public function test_retail_cannot_save_inc_fiscal_code(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RETAIL);

        $payload = $this->baseProductPayload($commerce);
        $payload['product']['fiscal_code'] = 'inc_8_preparado';

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product.fiscal_code']);
    }

    public function test_franchised_restaurant_cannot_save_inc_fiscal_code(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RESTAURANT, true);

        $payload = $this->baseProductPayload($commerce);
        $payload['product']['fiscal_code'] = 'inc_8_preparado';

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product.fiscal_code']);
    }

    public function test_independent_restaurant_can_save_inc_fiscal_code(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RESTAURANT, false);

        $payload = $this->baseProductPayload($commerce);
        $payload['product']['fiscal_code'] = 'inc_8_preparado';

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('products', [
            'title' => 'Producto Test',
            'fiscal_code' => 'inc_8_preparado',
            'applies_inc' => 1,
            'inc_rate' => 8.0,
        ]);
    }

    public function test_update_preserves_existing_fiscal_code_when_not_resent(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RETAIL);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'iva_19_general',
            'vat_rate' => 19.0,
            'original_price' => 100,
            'discounted_price' => 90,
        ]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'title' => 'Nuevo titulo'],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'fiscal_code' => 'iva_19_general']);
    }

    public function test_update_rejects_fiscal_code_not_allowed_for_commerce(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RETAIL);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'iva_19_general',
            'original_price' => 100,
            'discounted_price' => 90,
        ]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'inc_8_preparado'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product.fiscal_code']);
    }

    public function test_package_does_not_require_fiscal_code(): void
    {
        $user = $this->actingAsProvider();
        $commerce = $this->commerceOfType($user, Constant::ESTABLISHMENT_TYPE_RETAIL);

        $component = Product::factory()->create(['commerce_id' => $commerce->id]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => 'Combo Test',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $component->currentSalePrice(),
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'title' => 'Combo Test',
            'fiscal_code' => null,
        ]);
    }
}
