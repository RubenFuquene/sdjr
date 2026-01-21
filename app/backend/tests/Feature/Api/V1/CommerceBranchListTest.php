<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommerceBranchListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.commerces.view', 'guard_name' => 'sanctum']);
    }

    public function test_list_branches_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.view');
        $commerce = Commerce::factory()->create();
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
        $commerce = Commerce::factory()->create();
        CommerceBranch::factory()->count(2)->create(['commerce_id' => $commerce->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$commerce->id}/branches")
            ->assertForbidden();
    }

    public function test_list_branches_not_found_for_invalid_commerce(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.view');
        $invalidId = 99999;

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$invalidId}/branches")
            ->assertStatus(404);
    }
}
