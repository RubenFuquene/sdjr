<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

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
        // Crear el permiso si no existe
        Permission::findOrCreate('provider.products.delete', 'sanctum');
    }

    public function test_remove_photo_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.delete');
        $this->actingAs($user, 'sanctum');
        $photo = ProductPhoto::factory()->create();

        $response = $this->deleteJson('/api/v1/products/photos/'.$photo->id);
        $response->assertStatus(204);
        $this->assertSoftDeleted('product_photos', ['id' => $photo->id]);
    }

    public function test_remove_photo_not_found(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.delete');
        $this->actingAs($user, 'sanctum');
        $response = $this->deleteJson('/api/v1/products/photos/999999');
        $response->assertStatus(404);
    }
}
