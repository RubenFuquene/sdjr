<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LegalDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'legal_documents.index', 'guard_name' => 'sanctum']);
    }

    public function test_index_legal_documents_success(): void
    {
        
        $user = User::factory()->create();
        $user->givePermissionTo('legal_documents.index');
        LegalDocument::factory()->count(3)->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/legal-documents');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'title', 'content', 'version', 'status', 'effective_date', 'created_at', 'updated_at']
            ],
            'meta', 'links'
        ]);
    }

    public function test_index_legal_documents_forbidden(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/legal-documents');
        $response->assertForbidden();
    }

    public function test_show_by_type_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('legal_documents.index');
        $doc = LegalDocument::factory()->create(['type' => 'terms', 'status' => 'active']);
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/legal-documents/terms');
        $response->assertOk();
        $response->assertJsonFragment(['id' => $doc->id]);
    }

    public function test_show_by_type_not_found(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('legal_documents.index');
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/legal-documents/privacy');
        $response->assertNotFound();
    }

    public function test_show_by_type_forbidden(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/legal-documents/terms');
        $response->assertForbidden();
    }
}
