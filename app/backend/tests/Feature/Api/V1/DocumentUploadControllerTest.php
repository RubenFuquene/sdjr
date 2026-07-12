<?php

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\MockS3DiskTrait;

class DocumentUploadControllerTest extends TestCase
{
    use MockS3DiskTrait, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'admin.providers.upload_documents', 'guard_name' => 'sanctum']);
        $this->setUpMockS3Disk();
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

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

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
                'status',
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

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Document not found',
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

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

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

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(410)
            ->assertJson([
                'message' => 'The presigned URL has expired.',
            ]);
    }

    /**
     * @return void
     */
    public function test_confirm_supersedes_previous_document_of_same_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create();

        $previousDocument = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'CAMARA_COMERCIO',
            'upload_status' => 'confirmed',
            'version_number' => 1,
        ]);

        $newDocument = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'CAMARA_COMERCIO',
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'upload_status' => 'pending',
        ]);

        $payload = [
            'upload_token' => $newDocument->upload_token,
            's3_metadata' => [
                'etag' => 'etag-new',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(200);

        $this->assertSoftDeleted('commerce_documents', ['id' => $previousDocument->id]);

        $newDocument->refresh();
        $this->assertSame('confirmed', $newDocument->upload_status);
        $this->assertSame($previousDocument->id, $newDocument->replacement_of_id);
        $this->assertSame($previousDocument->id, $newDocument->version_of_id);
        $this->assertSame(2, $newDocument->version_number);
    }

    /**
     * @return void
     */
    public function test_confirmed_documents_list_excludes_superseded_document()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create();

        CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'CAMARA_COMERCIO',
            'upload_status' => 'confirmed',
            'version_number' => 1,
        ]);

        $newDocument = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'CAMARA_COMERCIO',
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'upload_status' => 'pending',
        ]);

        $payload = [
            'upload_token' => $newDocument->upload_token,
            's3_metadata' => [
                'etag' => 'etag-new',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $this->patchJson('/api/v1/documents/confirm', $payload)->assertStatus(200);

        $confirmedDocuments = $commerce->getConfirmedCommerceDocuments();

        $this->assertCount(1, $confirmedDocuments);
        $this->assertSame($newDocument->id, $confirmedDocuments->first()->id);
    }

    /**
     * @return void
     */
    public function test_confirm_does_not_affect_document_of_different_type()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create();

        $existingRutDocument = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'RUT',
            'upload_status' => 'confirmed',
            'version_number' => 1,
        ]);

        $newDocument = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'CAMARA_COMERCIO',
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'upload_status' => 'pending',
            'version_number' => 1,
        ]);

        $payload = [
            'upload_token' => $newDocument->upload_token,
            's3_metadata' => [
                'etag' => 'etag-new',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $this->patchJson('/api/v1/documents/confirm', $payload)->assertStatus(200);

        $existingRutDocument->refresh();
        $this->assertNull($existingRutDocument->deleted_at);
        $this->assertSame(1, $existingRutDocument->version_number);

        $newDocument->refresh();
        $this->assertNull($newDocument->replacement_of_id);
        $this->assertSame(1, $newDocument->version_number);
    }

    /**
     * @return void
     */
    public function test_confirm_chains_three_versions_of_the_same_document()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create();

        $v1 = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'ID_CARD',
            'upload_status' => 'confirmed',
            'version_number' => 1,
        ]);

        $v2 = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'ID_CARD',
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'upload_status' => 'pending',
        ]);

        $this->patchJson('/api/v1/documents/confirm', [
            'upload_token' => $v2->upload_token,
            's3_metadata' => [
                'etag' => 'etag-v2',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ])->assertStatus(200);

        $v3 = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'document_type' => 'ID_CARD',
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'upload_status' => 'pending',
        ]);

        $this->patchJson('/api/v1/documents/confirm', [
            'upload_token' => $v3->upload_token,
            's3_metadata' => [
                'etag' => 'etag-v3',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ])->assertStatus(200);

        $this->assertSoftDeleted('commerce_documents', ['id' => $v1->id]);
        $this->assertSoftDeleted('commerce_documents', ['id' => $v2->id]);

        $v2->refresh();
        $v3->refresh();

        $this->assertSame($v1->id, $v2->replacement_of_id);
        $this->assertSame($v1->id, $v2->version_of_id);
        $this->assertSame(2, $v2->version_number);

        $this->assertSame($v2->id, $v3->replacement_of_id);
        $this->assertSame($v1->id, $v3->version_of_id);
        $this->assertSame(3, $v3->version_number);

        $confirmedDocuments = $commerce->getConfirmedCommerceDocuments();
        $this->assertCount(1, $confirmedDocuments);
        $this->assertSame($v3->id, $confirmedDocuments->first()->id);
    }

    /**
     * @return void
     */
    public function test_confirm_first_document_of_its_type_does_not_supersede_anything()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $document = CommerceDocument::factory()->create([
            'document_type' => 'CAMARA_COMERCIO',
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
            'upload_status' => 'pending',
            'version_number' => 1,
        ]);

        $payload = [
            'upload_token' => $document->upload_token,
            's3_metadata' => [
                'etag' => 'etag12345',
                'object_size' => 2048,
                'last_modified' => now()->toIso8601String(),
            ],
        ];

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(200);

        $document->refresh();
        $this->assertSame('confirmed', $document->upload_status);
        $this->assertNull($document->replacement_of_id);
        $this->assertSame(1, $document->version_number);
    }

    /**
     * @return void
     */
    public function test_provider_can_upload_rut_for_own_commerce()
    {
        Permission::create(['name' => 'provider.documents.upload', 'guard_name' => 'sanctum']);

        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.upload');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $payload = [
            'commerce_id' => $commerce->id,
            'document_type' => 'RUT',
            'file_name' => 'rut.pdf',
            'mime_type' => 'pdf',
            'file_size_bytes' => 1024,
        ];

        $response = $this->postJson('/api/v1/documents/presigned', $payload);

        $response->assertStatus(201);
    }

    /**
     * @return void
     */
    public function test_provider_cannot_upload_document_for_foreign_commerce()
    {
        Permission::create(['name' => 'provider.documents.upload', 'guard_name' => 'sanctum']);

        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.upload');
        $this->actingAs($user);

        $foreignCommerce = Commerce::factory()->create();

        $payload = [
            'commerce_id' => $foreignCommerce->id,
            'document_type' => 'RUT',
            'file_name' => 'rut.pdf',
            'mime_type' => 'pdf',
            'file_size_bytes' => 1024,
        ];

        $response = $this->postJson('/api/v1/documents/presigned', $payload);

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function test_provider_without_permission_cannot_upload_document()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $payload = [
            'commerce_id' => $commerce->id,
            'document_type' => 'RUT',
            'file_name' => 'rut.pdf',
            'mime_type' => 'pdf',
            'file_size_bytes' => 1024,
        ];

        $response = $this->postJson('/api/v1/documents/presigned', $payload);

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function test_provider_cannot_upload_1876_when_not_required_to_invoice_electronically()
    {
        Permission::create(['name' => 'provider.documents.upload', 'guard_name' => 'sanctum']);

        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.upload');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create([
            'owner_user_id' => $user->id,
            'electronic_invoicing_required' => false,
        ]);

        $payload = [
            'commerce_id' => $commerce->id,
            'document_type' => '1876',
            'file_name' => 'formato-1876.pdf',
            'mime_type' => 'pdf',
            'file_size_bytes' => 1024,
        ];

        $response = $this->postJson('/api/v1/documents/presigned', $payload);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function test_provider_can_upload_1876_when_required_to_invoice_electronically()
    {
        Permission::create(['name' => 'provider.documents.upload', 'guard_name' => 'sanctum']);

        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.upload');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create([
            'owner_user_id' => $user->id,
            'electronic_invoicing_required' => true,
        ]);

        $payload = [
            'commerce_id' => $commerce->id,
            'document_type' => '1876',
            'file_name' => 'formato-1876.pdf',
            'mime_type' => 'pdf',
            'file_size_bytes' => 1024,
        ];

        $response = $this->postJson('/api/v1/documents/presigned', $payload);

        $response->assertStatus(201);
    }

    /**
     * @return void
     */
    public function test_provider_can_confirm_document_of_own_commerce()
    {
        Permission::create(['name' => 'provider.documents.upload', 'guard_name' => 'sanctum']);

        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.upload');
        $this->actingAs($user);

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        $document = CommerceDocument::factory()->create([
            'commerce_id' => $commerce->id,
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
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

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function test_provider_cannot_confirm_document_of_foreign_commerce()
    {
        Permission::create(['name' => 'provider.documents.upload', 'guard_name' => 'sanctum']);

        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.upload');
        $this->actingAs($user);

        $foreignCommerce = Commerce::factory()->create();

        $document = CommerceDocument::factory()->create([
            'commerce_id' => $foreignCommerce->id,
            'upload_token' => Str::uuid()->toString(),
            'expires_at' => now()->addHour(),
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

        $response = $this->patchJson('/api/v1/documents/confirm', $payload);

        $response->assertStatus(403);
    }

    /**
     * Regresión del retiro de admin.providers.upload_documents del rol provider
     * (Tarea 2, SCRUM-242): el usuario con el permiso admin sigue operando
     * cualquier comercio sin verificación de ownership.
     *
     * @return void
     */
    public function test_admin_permission_bypasses_ownership_check()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.upload_documents');
        $this->actingAs($user);

        $foreignCommerce = Commerce::factory()->create();

        $payload = [
            'commerce_id' => $foreignCommerce->id,
            'document_type' => 'RUT',
            'file_name' => 'rut.pdf',
            'mime_type' => 'pdf',
            'file_size_bytes' => 1024,
        ];

        $response = $this->postJson('/api/v1/documents/presigned', $payload);

        $response->assertStatus(201);
    }
}
