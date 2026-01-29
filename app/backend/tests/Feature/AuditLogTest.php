<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\AuditLogController;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Covers;
use Tests\TestCase;

#[Covers(AuditLogController::class)]
class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_audit_logs(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs');
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id', 'user_id', 'method', 'endpoint', 'payload', 'response_code', 'response_time', 'ip_address', 'user_agent', 'created_at', 'updated_at',
            ],
        ]);
    }

    public function test_unauthenticated_user_cannot_list_audit_logs(): void
    {
        $response = $this->getJson('/api/v1/audit-logs');
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_view_a_single_audit_log(): void
    {
        $user = User::factory()->create();
        $log = AuditLog::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/audit-logs/'.$log->id);
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $log->id,
            'endpoint' => $log->endpoint,
        ]);
    }

    public function test_not_found_for_nonexistent_audit_log(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/audit-logs/99999');
        $response->assertNotFound();
    }
}
