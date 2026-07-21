<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_product_is_returned_with_category_name_and_commerce_name()
    {
        $commerce = Commerce::factory()->create(['name' => 'Panaderia El Trigal']);
        $category = ProductCategory::factory()->create(['name' => 'Panaderia']);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
            'status' => Constant::STATUS_ACTIVE,
            'description' => 'Bolsa sorpresa con pan del dia',
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->id}");

        $response->assertOk();
        // Commerce::sanitizeText normaliza el nombre a "lower + ucfirst" (comportamiento existente del modelo).
        $response->assertJsonFragment([
            'id' => $product->id,
            'category' => 'Panaderia',
            'commerce_name' => 'Panaderia el trigal',
            'description' => 'Bolsa sorpresa con pan del dia',
        ]);
    }

    public function test_inactive_product_is_not_visible_by_id()
    {
        $product = Product::factory()->create(['status' => Constant::STATUS_INACTIVE]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->id}");

        $response->assertNotFound();
    }

    public function test_nonexistent_product_returns_404()
    {
        $response = $this->getJson('/api/v1/catalog/products/999999');

        $response->assertNotFound();
    }

    public function test_active_branch_is_returned_with_commerce_name()
    {
        $commerce = Commerce::factory()->create(['name' => 'Cafe Amor Perfecto']);
        $branch = CommerceBranch::factory()->create([
            'commerce_id' => $commerce->id,
            'status' => Constant::STATUS_ACTIVE,
        ]);

        $response = $this->getJson("/api/v1/catalog/commerce-branches/{$branch->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $branch->id,
            'commerce_name' => 'Cafe amor perfecto',
        ]);
    }

    public function test_inactive_branch_is_not_visible_by_id()
    {
        $branch = CommerceBranch::factory()->create(['status' => Constant::STATUS_INACTIVE]);

        $response = $this->getJson("/api/v1/catalog/commerce-branches/{$branch->id}");

        $response->assertNotFound();
    }

    public function test_nonexistent_branch_returns_404()
    {
        $response = $this->getJson('/api/v1/catalog/commerce-branches/999999');

        $response->assertNotFound();
    }

    public function test_catalog_endpoints_do_not_require_authentication()
    {
        $product = Product::factory()->create(['status' => Constant::STATUS_ACTIVE]);
        $branch = CommerceBranch::factory()->create(['status' => Constant::STATUS_ACTIVE]);

        $this->getJson("/api/v1/catalog/products/{$product->id}")->assertOk();
        $this->getJson("/api/v1/catalog/commerce-branches/{$branch->id}")->assertOk();
    }
}
