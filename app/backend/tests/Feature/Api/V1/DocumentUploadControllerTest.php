<?php

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentUploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'admin.providers.upload_documents', 'guard_name' => 'sanctum']);
    }

    /**
     * @return void
     */
    public function test_generate_presigned_url_success()
    {
        $user = User::factory()->create();
        // Asignar permiso necesario
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $payload = [
            'commerce_id' => Commerce::factory()->create()->id,
            'document_type' => 'ID_CARD',
            'file_name' => 'documento.pdf',
            'mime_type' => 'pdf',
            'file_size_bytes' => 1024,
        ];

        $response = $this->postJson('/api/v1/documents/presigned', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'upload_token',
                    'presigned_url',
                    'expires_in',
                    'path',
                ],
                'message',
            ]);
    }

    /**
     * @return void
     */
    public function test_confirm_document_upload_success()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        // Simular documento pendiente
        $document = CommerceDocument::factory()->create([
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'upload_status' => 'pending',
            'mime_type' => 'pdf',
        ]);

        $payload = [
            'upload_token' => $document->upload_token,
            's3_metadata' => [
                'etag' => 'etag12345',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->postJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'commerce_id',
                    'document_type',
                    'upload_status',
                    's3_etag',
                    's3_object_size',
                    'file_path',
                    'uploaded_by_id',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);
    }

    /**
     * @return void
     */
    public function test_document_token_not_found()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $payload = [
            'upload_token' => 'non-existent-token',
            's3_metadata' => [
                'etag' => 'etag12345',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->postJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The selected upload token is invalid.',
            ]);
    }

    /**
     * @return void
     */
    public function test_token_exists_but_not_pending()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        // Simular documento que no está en estado pendiente
        $document = CommerceDocument::factory()->create([
            'upload_token' => Str::uuid()->toString(),
            'upload_status' => 'confirmed',
        ]);

        $payload = [
            'upload_token' => $document->upload_token,
            's3_metadata' => [
                'etag' => 'etag12345',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->postJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Document not found',
            ]);
    }

    /**
     * @return void
     */
    public function test_presigned_url_expired()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);
        // Simular documento pendiente pero expirado
        $document = CommerceDocument::factory()->create([
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->subHour(),
            'upload_status' => 'pending',
        ]);
        $payload = [
            'upload_token' => $document->upload_token,
            's3_metadata' => [
                'etag' => 'etag12345',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->postJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(410)
            ->assertJson([
                'message' => 'The presigned URL has expired.',
            ]);
    }
}
