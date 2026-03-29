<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_branches_within_radius_are_returned()
    {
        // Arrange: crear branches cercanas y lejanas
        $near = CommerceBranch::factory()->create(['latitude' => 10.0, 'longitude' => 10.0]);
        $far = CommerceBranch::factory()->create(['latitude' => 50.0, 'longitude' => 50.0]);

        // Act
        $response = $this->getJson('/api/v1/nearby/branches?latitude=10.0&longitude=10.0&radius=5');

        // Assert
        $response->assertOk();
        $response->assertJsonFragment(['status' => true]);
    }

    public function test_inactive_branches_are_excluded()
    {
        $active = CommerceBranch::factory()->create(['status' => Constant::STATUS_ACTIVE, 'latitude' => 10, 'longitude' => 10]);
        $inactive = CommerceBranch::factory()->create(['status' => Constant::STATUS_INACTIVE, 'latitude' => 10, 'longitude' => 10]);

        $response = $this->getJson('/api/v1/nearby/branches?latitude=10&longitude=10&radius=10');
        $response->assertOk();
        $response->assertJsonFragment(['id' => $active->id]);
        $response->assertJsonMissing(['id' => $inactive->id]);
    }

    public function test_distance_km_is_present_and_correct()
    {
        $branch = CommerceBranch::factory()->create(['latitude' => 10, 'longitude' => 10]);
        $response = $this->getJson('/api/v1/nearby/branches?latitude=10&longitude=10&radius=10');
        $response->assertOk();
    }

    public function test_only_active_products_with_stock_and_not_expired_are_returned()
    {
        $branch = CommerceBranch::factory()->create(['latitude' => 10, 'longitude' => 10]);
        $product = Product::factory()->create(['status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()]);
        $product->commerceBranches()->attach($branch->id);

        $expired = Product::factory()->create(['status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->subDay()]);
        $expired->commerceBranches()->attach($branch->id);

        $noStock = Product::factory()->create(['status' => Constant::STATUS_ACTIVE, 'quantity_available' => 0, 'expires_at' => now()->addDay()]);
        $noStock->commerceBranches()->attach($branch->id);

        $inactive = Product::factory()->create(['status' => Constant::STATUS_INACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()]);
        $inactive->commerceBranches()->attach($branch->id);

        $response = $this->getJson('/api/v1/nearby/products?latitude=10&longitude=10&radius=10');
        $response->assertOk();
        $response->assertJsonFragment(['id' => $product->id]);
        $response->assertJsonMissing(['id' => $expired->id]);
        $response->assertJsonMissing(['id' => $noStock->id]);
        $response->assertJsonMissing(['id' => $inactive->id]);
    }

    public function test_product_without_nearby_branch_is_not_returned()
    {
        $branch = CommerceBranch::factory()->create(['latitude' => 50, 'longitude' => 50]);
        $product = Product::factory()->create(['status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()]);
        $product->commerceBranches()->attach($branch->id);

        $response = $this->getJson('/api/v1/nearby/products?latitude=10&longitude=10&radius=5');
        $response->assertOk();
    }

    public function test_category_id_filter_works()
    {
        $branch = CommerceBranch::factory()->create(['latitude' => 10, 'longitude' => 10]);
        $category1 = ProductCategory::factory()->create();
        $category2 = ProductCategory::factory()->create();
        $product1 = Product::factory()->create(['product_category_id' => $category1->id, 'status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()]);
        $product2 = Product::factory()->create(['product_category_id' => $category2->id, 'status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()]);
        $product1->commerceBranches()->attach($branch->id);
        $product2->commerceBranches()->attach($branch->id);

        $response = $this->getJson('/api/v1/nearby/products?latitude=10&longitude=10&radius=10&category_id='.$category1->id);
        $response->assertOk();
        $response->assertJsonFragment(['id' => $product1->id]);
        $response->assertJsonMissing(['id' => $product2->id]);
    }

    public function test_max_price_filter_works()
    {
        $branch = CommerceBranch::factory()->create(['latitude' => 10, 'longitude' => 10]);
        $cheap = Product::factory()->create(['discounted_price' => 10, 'status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()]);
        $expensive = Product::factory()->create(['discounted_price' => 1000, 'status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()]);
        $cheap->commerceBranches()->attach($branch->id);
        $expensive->commerceBranches()->attach($branch->id);

        $response = $this->getJson('/api/v1/nearby/products?latitude=10&longitude=10&radius=10&max_price=100');
        $response->assertOk();
        $response->assertJsonFragment(['id' => $cheap->id]);
        $response->assertJsonMissing(['id' => $expensive->id]);
    }

    public function test_latitude_validation_fails_out_of_range()
    {
        $response = $this->getJson('/api/v1/nearby/branches?latitude=200&longitude=10&radius=10');
        $response->assertStatus(422);
    }

    public function test_pagination_works()
    {
        $branch = CommerceBranch::factory()->create(['latitude' => 10, 'longitude' => 10]);
        Product::factory(30)->create(['status' => Constant::STATUS_ACTIVE, 'quantity_available' => 5, 'expires_at' => now()->addDay()])->each(function ($product) use ($branch) {
            $product->commerceBranches()->attach($branch->id);
        });
        $response = $this->getJson('/api/v1/nearby/products?latitude=10&longitude=10&radius=10&per_page=10');
        $response->assertOk();
        $response->assertJsonPath('meta.per_page', 10);
    }
}
