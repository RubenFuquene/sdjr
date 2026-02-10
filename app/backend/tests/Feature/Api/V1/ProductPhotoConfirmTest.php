<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\ProductPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductPhotoConfirmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear el permiso si no existe
        Permission::findOrCreate('provider.photos.upload', 'sanctum');
    }

    public function test_confirm_photo_upload_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.upload');
        $this->actingAs($user, 'sanctum');

        $photo = ProductPhoto::factory()->create([
            'upload_status' => Constant::UPLOAD_STATUS_PENDING,
            'upload_token' => Str::random(32),
            'expires_at' => now()->addHour(),
        ]);

        $payload = [
            'upload_token' => $photo->upload_token,
            's3_metadata' => [
                'etag' => 'etag123',
                'object_size' => 123456,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->patchJson('/api/v1/products/photos/confirm', $payload);
        $response->assertStatus(200)
            ->assertJsonFragment(['upload_status' => Constant::UPLOAD_STATUS_CONFIRMED]);
    }

    public function test_confirm_photo_upload_expired_url(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.upload');
        $this->actingAs($user, 'sanctum');

        $photo = ProductPhoto::factory()->create([
            'upload_status' => Constant::UPLOAD_STATUS_PENDING,
            'upload_token' => Str::random(32),
            'expires_at' => now()->subMinute(),
        ]);

        $payload = [
            'upload_token' => $photo->upload_token,
            's3_metadata' => [
                'etag' => 'etag123',
                'object_size' => 123456,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->patchJson('/api/v1/products/photos/confirm', $payload);
        $response->assertStatus(410);
    }

    public function test_confirm_photo_upload_not_found(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.upload');
        $this->actingAs($user, 'sanctum');

        $payload = [
            'upload_token' => 'nonexistenttoken',
            's3_metadata' => [
                'etag' => 'etag123',
                'object_size' => 123456,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->patchJson('/api/v1/products/photos/confirm', $payload);
        $response->assertStatus(404);
    }
}
