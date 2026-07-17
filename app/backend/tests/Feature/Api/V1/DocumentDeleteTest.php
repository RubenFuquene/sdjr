<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-316 — DestroyDocumentUploadRequest validaba provider.products.delete (permiso
 * ajeno a documentos, sin ownership). Ahora exige provider.documents.delete + ownership.
 */
class DocumentDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.documents.delete', 'sanctum');
        Permission::findOrCreate('provider.products.delete', 'sanctum');
        Permission::findOrCreate('admin.providers.documents.manage', 'sanctum');
    }

    private function documentForCommerce(int $commerceId): CommerceDocument
    {
        return CommerceDocument::factory()->create([
            'commerce_id' => $commerceId,
            'upload_status' => Constant::UPLOAD_STATUS_CONFIRMED,
        ]);
    }

    public function test_owner_with_documents_delete_permission_can_delete(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.delete');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $document = $this->documentForCommerce($commerce->id);

        $response = $this->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('commerce_documents', ['id' => $document->id]);
    }

    public function test_foreign_provider_cannot_delete(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.delete');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $document = $this->documentForCommerce($foreignCommerce->id);

        $response = $this->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('commerce_documents', ['id' => $document->id, 'deleted_at' => null]);
    }

    public function test_products_delete_permission_alone_is_no_longer_sufficient(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.delete');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $document = $this->documentForCommerce($commerce->id);

        $response = $this->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(403);
    }

    public function test_admin_permission_bypasses_ownership_check(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.documents.manage');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $document = $this->documentForCommerce($foreignCommerce->id);

        $response = $this->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(204);
    }

    public function test_nonexistent_document_returns_404(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.delete');
        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson('/api/v1/documents/999999');

        $response->assertStatus(404);
    }
}
