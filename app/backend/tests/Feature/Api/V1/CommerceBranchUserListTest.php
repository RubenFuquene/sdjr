<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\CommerceBranchUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * SCRUM-334: getCommerceUsers()/getCommerceBranchUsers() no validaban
 * propiedad, solo rol — cualquier provider veía los líderes (nombre, email,
 * teléfono) de cualquier comercio o sucursal. Sin cobertura previa.
 */
class CommerceBranchUserListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('provider', 'sanctum');
        Role::findOrCreate('branch_leader', 'sanctum');
        Role::findOrCreate('admin', 'sanctum');
        Role::findOrCreate('superadmin', 'sanctum');
    }

    public function test_index_lists_branch_leaders_for_own_commerce(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        CommerceBranchUser::factory()->create(['commerce_id' => $commerce->id, 'commerce_branch_id' => $branch->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/commerce-branch-users?commerce_id='.$commerce->id);

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /**
     * SCRUM-334 (IDOR): un provider no puede listar los líderes de un comercio ajeno.
     */
    public function test_index_fails_for_a_commerce_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        CommerceBranchUser::factory()->create(['commerce_id' => $commerce->id, 'commerce_branch_id' => $branch->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/commerce-branch-users?commerce_id='.$commerce->id);

        $response->assertForbidden();
    }

    public function test_index_allows_superadmin_for_any_commerce(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        $commerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        CommerceBranchUser::factory()->create(['commerce_id' => $commerce->id, 'commerce_branch_id' => $branch->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/commerce-branch-users?commerce_id='.$commerce->id);

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_show_branch_users_allows_commerce_owner(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        CommerceBranchUser::factory()->create(['commerce_id' => $commerce->id, 'commerce_branch_id' => $branch->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/commerce-branch-users/commerce-branch/'.$branch->id);

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /**
     * SCRUM-334 (IDOR): un provider no puede ver los usuarios de una sucursal ajena.
     */
    public function test_show_branch_users_fails_for_a_branch_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $branch = CommerceBranch::factory()->create();
        CommerceBranchUser::factory()->create(['commerce_id' => $branch->commerce_id, 'commerce_branch_id' => $branch->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/commerce-branch-users/commerce-branch/'.$branch->id);

        $response->assertForbidden();
    }

    /**
     * Un branch_leader asignado a la sucursal puede ver sus compañeros, aunque no sea el dueño del comercio.
     */
    public function test_show_branch_users_allows_a_branch_leader_assigned_to_that_branch(): void
    {
        $leader = User::factory()->create();
        $leader->assignRole('branch_leader');
        $branch = CommerceBranch::factory()->create();
        CommerceBranchUser::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'commerce_branch_id' => $branch->id,
            'user_id' => $leader->id,
        ]);

        $response = $this->actingAs($leader, 'sanctum')
            ->getJson('/api/v1/commerce-branch-users/commerce-branch/'.$branch->id);

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /**
     * SCRUM-334 (IDOR): un branch_leader asignado a OTRA sucursal no puede ver esta.
     */
    public function test_show_branch_users_fails_for_a_branch_leader_assigned_to_a_different_branch(): void
    {
        $leader = User::factory()->create();
        $leader->assignRole('branch_leader');
        $ownBranch = CommerceBranch::factory()->create();
        CommerceBranchUser::factory()->create([
            'commerce_id' => $ownBranch->commerce_id,
            'commerce_branch_id' => $ownBranch->id,
            'user_id' => $leader->id,
        ]);
        $otherBranch = CommerceBranch::factory()->create();

        $response = $this->actingAs($leader, 'sanctum')
            ->getJson('/api/v1/commerce-branch-users/commerce-branch/'.$otherBranch->id);

        $response->assertForbidden();
    }
}
