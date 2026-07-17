<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-316 (ampliado) — DELETE /commerce-branches/photos/{photo} compartía
 * DestroyDocumentUploadRequest (permiso provider.products.delete, sin ownership real).
 * Ahora exige provider.photos.delete + ownership del comercio dueño de la sucursal.
 *
 * Nota: el controller resuelve la "foto" vía CommerceBranch::class (bug funcional
 * documentado en SCRUM-273, fuera de alcance aquí) — por eso el recurso operado es
 * la sucursal misma.
 */
class CommerceBranchPhotoDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.photos.delete', 'sanctum');
        Permission::findOrCreate('admin.providers.documents.manage', 'sanctum');
    }

    public function test_owner_can_delete_branch_photo(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.delete');
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $response = $this->deleteJson("/api/v1/commerce-branches/photos/{$branch->id}");

        $response->assertStatus(204);
    }

    public function test_foreign_provider_cannot_delete_branch_photo(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.delete');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $foreignCommerce->id]);

        $response = $this->deleteJson("/api/v1/commerce-branches/photos/{$branch->id}");

        $response->assertStatus(403);
    }

    public function test_user_without_permission_cannot_delete_branch_photo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $response = $this->deleteJson("/api/v1/commerce-branches/photos/{$branch->id}");

        $response->assertStatus(403);
    }

    public function test_admin_permission_bypasses_ownership_check(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.providers.documents.manage');
        $this->actingAs($user, 'sanctum');

        $foreignCommerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $foreignCommerce->id]);

        $response = $this->deleteJson("/api/v1/commerce-branches/photos/{$branch->id}");

        $response->assertStatus(204);
    }

    public function test_nonexistent_photo_returns_404(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.photos.delete');
        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson('/api/v1/commerce-branches/photos/999999');

        $response->assertStatus(404);
    }
}
