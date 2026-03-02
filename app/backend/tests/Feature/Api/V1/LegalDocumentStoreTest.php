<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LegalDocumentStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'legal_documents.create', 'guard_name' => 'sanctum']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_legal_document_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('legal_documents.create');
        $this->actingAs($user, 'sanctum');
        $payload = [
            'type' => 'terms',
            'title' => 'Términos y condiciones',
            'content' => '<html>...</html>',
            'version' => 'v1.0',
            'status' => 'active',
            'effective_date' => '2026-02-25',
        ];
        $response = $this->postJson('/api/v1/legal-documents', $payload);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'type', 'title', 'content', 'version', 'status', 'effective_date', 'created_at', 'updated_at',
                ],
            ]);
        $this->assertDatabaseHas('legal_documents', ['type' => 'terms', 'title' => 'Términos y condiciones']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_legal_document_validation_error(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('legal_documents.create');
        $this->actingAs($user, 'sanctum');
        $payload = [
            'type' => '',
            'title' => '',
            'content' => '',
            'status' => '',
        ];
        $response = $this->postJson('/api/v1/legal-documents', $payload);
        $response->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_legal_document_unauthorized(): void
    {
        $response = $this->postJson('/api/v1/legal-documents', []);
        $response->assertStatus(401);
    }
}
