<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\MockS3DiskTrait;

class ProductStoreTest extends TestCase
{
    use MockS3DiskTrait, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear el permiso si no existe
        Permission::findOrCreate('provider.products.create', 'sanctum');
        $this->setUpMockS3Disk();
    }

    public function test_store_product_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.create');
        $this->actingAs($user, 'sanctum');

        // El usuario debe ser owner del commerce
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();

        // Crear branch asociado al commerce y al usuario owner
        $branch = CommerceBranch::factory()->create([
            'commerce_id' => $commerce->id,
        ]);

        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Producto Test',
                'description' => 'Descripción de prueba',
                'product_type' => 'single',
                'original_price' => 100.0,
                'discounted_price' => 90.0,
                'quantity_total' => 10,
                'quantity_available' => 10,
                'expires_at' => now()->addMonth()->toIso8601String(),
                'status' => '1',
            ],
            'photos' => [
                [
                    'file_name' => 'test.jpg',
                    'mime_type' => 'jpeg',
                    'file_size_bytes' => 50000,
                    'versioning_enabled' => 'false',
                    'metadata' => ['description' => 'Foto de prueba'],
                ],
            ],
            'commerce_branch_ids' => [$branch->id],
        ];

        $response = $this->postJson('/api/v1/products', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Producto Test']);
        $this->assertDatabaseHas('products', ['title' => 'Producto Test']);
    }

    public function test_store_product_validation_error(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.create');
        $this->actingAs($user, 'sanctum');
        // El usuario debe ser owner del commerce
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create([
            'commerce_id' => $commerce->id,
        ]);
        $payload = [
            'product' => [
                'commerce_id' => $commerce->id,
                'title' => '',
            ],
            'commerce_branch_ids' => [$branch->id],
        ];
        $response = $this->postJson('/api/v1/products', $payload);
        $response->assertStatus(422);
    }
}
