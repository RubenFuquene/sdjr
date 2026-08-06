<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommerceBranchListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.branches.show', 'guard_name' => 'sanctum']);
    }

    public function test_list_branches_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.show');
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        CommerceBranch::factory()->count(3)->create(['commerce_id' => $commerce->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$commerce->id}/branches")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'commerce_id', 'name', 'address', 'department', 'city', 'neighborhood', 'latitude', 'longitude', 'phone', 'email', 'is_active', 'created_at', 'updated_at',
                    ],
                ],
                'meta', 'links',
            ]);
    }

    public function test_list_branches_forbidden_without_permission(): void
    {
        $user = User::factory()->create();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        CommerceBranch::factory()->count(2)->create(['commerce_id' => $commerce->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$commerce->id}/branches")
            ->assertForbidden();
    }

    /**
     * SCRUM-334 (IDOR): un aliado no puede listar las sucursales de un comercio ajeno.
     */
    public function test_list_branches_fails_for_a_commerce_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.show');
        $commerce = Commerce::factory()->create();
        CommerceBranch::factory()->count(2)->create(['commerce_id' => $commerce->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$commerce->id}/branches")
            ->assertForbidden();
    }

    public function test_list_branches_allows_superadmin_for_any_commerce(): void
    {
        Role::findOrCreate('superadmin', 'sanctum');
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        $admin->givePermissionTo('provider.branches.show');
        $commerce = Commerce::factory()->create();
        CommerceBranch::factory()->count(2)->create(['commerce_id' => $commerce->id]);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/commerces/{$commerce->id}/branches")
            ->assertOk();
    }

    /**
     * SCRUM-334: un commerce_id inexistente no puede distinguirse de uno
     * ajeno — la ownership check corre antes que la existencia, mismo
     * criterio anti-enumeración ya adoptado en el proyecto.
     */
    public function test_list_branches_fails_for_a_nonexistent_commerce(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.show');
        $invalidId = 99999;

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$invalidId}/branches")
            ->assertForbidden();
    }
}
