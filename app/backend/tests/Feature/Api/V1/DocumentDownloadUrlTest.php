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
 * SCRUM-315 — IDOR en download-url: el endpoint debe exigir ownership del comercio
 * dueño del documento (o permiso admin) antes de generar la URL firmada.
 */
class DocumentDownloadUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.documents.view', 'sanctum');
        Permission::findOrCreate('admin.providers.documents.manage', 'sanctum');
    }

    private function documentForCommerce(int $commerceId): CommerceDocument
    {
        return CommerceDocument::factory()->create([
            'commerce_id' => $commerceId,
            'upload_status' => Constant::UPLOAD_STATUS_CONFIRMED,
        ]);
    }

    public function test_owner_can_get_download_url(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.view');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $document = $this->documentForCommerce($commerce->id);

        $response = $this->postJson("/api/v1/documents/{$document->id}/download-url");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['document_id', 'url', 'expires_at', 'expired_in_seconds']]);
    }

    public function test_foreign_provider_cannot_get_download_url(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.view');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $document = $this->documentForCommerce($foreignCommerce->id);

        $response = $this->postJson("/api/v1/documents/{$document->id}/download-url");

        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_get_download_url(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $document = $this->documentForCommerce($commerce->id);

        $response = $this->postJson("/api/v1/documents/{$document->id}/download-url");

        $response->assertStatus(403);
    }

    public function test_admin_permission_bypasses_ownership_check(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.documents.manage');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $document = $this->documentForCommerce($foreignCommerce->id);

        $response = $this->postJson("/api/v1/documents/{$document->id}/download-url");

        $response->assertOk();
    }

    public function test_nonexistent_document_returns_404(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.documents.view');
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/documents/999999/download-url');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_get_download_url(): void
    {
        $commerce = Commerce::factory()->create();
        $document = $this->documentForCommerce($commerce->id);

        $response = $this->postJson("/api/v1/documents/{$document->id}/download-url");

        $response->assertStatus(401);
    }
}
