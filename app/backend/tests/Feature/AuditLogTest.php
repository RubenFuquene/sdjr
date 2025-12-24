<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\Api\V1\AuditLogController
 */
class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_list_audit_logs(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs');
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id', 'user_id', 'method', 'endpoint', 'payload', 'response_code', 'response_time', 'ip_address', 'user_agent', 'created_at', 'updated_at'
            ]
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_audit_logs(): void
    {
        $response = $this->getJson('/api/v1/audit-logs');
        $response->assertUnauthorized();
    }

    /** @test */
    public function authenticated_user_can_view_a_single_audit_log(): void
    {
        $user = User::factory()->create();
        $log = AuditLog::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs/' . $log->id);
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $log->id,
            'endpoint' => $log->endpoint,
        ]);
    }

    /** @test */
    public function not_found_for_nonexistent_audit_log(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/audit-logs/99999');
        $response->assertNotFound();
    }
}
