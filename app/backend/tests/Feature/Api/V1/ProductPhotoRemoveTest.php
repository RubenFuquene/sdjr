<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductPhotoRemoveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.photos.delete', 'sanctum');
        Permission::findOrCreate('admin.providers.documents.manage', 'sanctum');
    }

    public function test_remove_photo_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.delete');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $photo = ProductPhoto::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson('/api/v1/products/commerce/photos/'.$photo->id);
        $response->assertStatus(204);
        $this->assertSoftDeleted('product_photos', ['id' => $photo->id]);
    }

    public function test_remove_photo_not_found(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.delete');
        $this->actingAs($user, 'sanctum');
        $response = $this->deleteJson('/api/v1/products/commerce/photos/999999');
        $response->assertStatus(404);
    }

    public function test_remove_photo_of_foreign_commerce_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.delete');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $foreignCommerce->id]);
        $photo = ProductPhoto::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson('/api/v1/products/commerce/photos/'.$photo->id);
        $response->assertStatus(403);
        $this->assertDatabaseHas('product_photos', ['id' => $photo->id, 'deleted_at' => null]);
    }

    public function test_remove_photo_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $photo = ProductPhoto::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson('/api/v1/products/commerce/photos/'.$photo->id);
        $response->assertStatus(403);
    }

    public function test_admin_permission_bypasses_ownership_check(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.documents.manage');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $product = Product::factory()->create(['commerce_id' => $foreignCommerce->id]);
        $photo = ProductPhoto::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson('/api/v1/products/commerce/photos/'.$photo->id);
        $response->assertStatus(204);
    }
}
