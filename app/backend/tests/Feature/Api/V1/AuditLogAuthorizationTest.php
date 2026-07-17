<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Auditoría SCRUM-334 — AuditLogController::index/show no tenían ningún check de
 * autorización: cualquier usuario autenticado podía listar/leer todos los logs.
 */
class AuditLogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('admin.audit_logs.index', 'sanctum');
        Permission::findOrCreate('admin.audit_logs.show', 'sanctum');
    }

    public function test_user_without_permission_cannot_list_audit_logs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_list_audit_logs(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.audit_logs.index');
        $this->actingAs($user, 'sanctum');

        AuditLog::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertOk()->assertJsonCount(2);
    }

    public function test_user_without_permission_cannot_show_audit_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $log = AuditLog::factory()->create();

        $response = $this->getJson("/api/v1/audit-logs/{$log->id}");

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_show_audit_log(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.audit_logs.show');
        $this->actingAs($user, 'sanctum');

        $log = AuditLog::factory()->create();

        $response = $this->getJson("/api/v1/audit-logs/{$log->id}");

        $response->assertOk()->assertJsonPath('id', $log->id);
    }

    public function test_unauthenticated_user_cannot_access_audit_logs(): void
    {
        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertStatus(401);
    }
}
