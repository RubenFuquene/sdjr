<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Commerce;
use App\Models\Department;
use App\Models\City;
use App\Models\Neighborhood;
use App\Models\CommerceBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommerceBranchTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.commerces.create', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.commerces.update', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.commerces.view', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.commerces.delete', 'guard_name' => 'sanctum']);
    }

    public function test_create_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.create');
        $commerce = Commerce::factory()->create();
        $department = Department::factory()->create();
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create();
        $payload = [
            'commerce_branch' => [
                'commerce_id' => $commerce->id,
                'department_id' => $department->id,
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'name' => 'Sucursal Test',
                'address' => 'Calle 123',
                'latitude' => 4.6,
                'longitude' => -74.1,
                'phone' => '3001234567',
                'email' => 'sucursal@test.com',
                'status' => true,
            ],
            'commerce_branch_hours' => [
                'day_of_week' => 1,
                'open_time' => '08:00',
                'close_time' => '18:00',
                'note' => 'Horario normal',
            ],
            'commerce_branch_photos' => [
                'uploaded_by_id' => $user->id,
                'upload_token' => 'token123',
                's3_etag' => 'etag123',
                's3_object_size' => 123456,
                's3_last_modified' => '2026-01-20 10:00:00',
                'replacement_of_id' => null,
                'version_of_id' => null,
                'version_number' => 1,
                'expires_at' => null,
                'failed_attempts' => 0,
                'photo_type' => 'front',
                'file_path' => '/photos/branch1.jpg',
                'mime_type' => 'image/jpeg',
                'uploaded_at' => '2026-01-20 10:00:00',
            ]
        ];
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/commerce-branches', $payload)
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Sucursal test']);
    }

    public function test_update_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.update');
        $branch = CommerceBranch::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/commerce-branches/{$branch->id}", ['name' => 'Sucursal Editada'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Sucursal editada']);
    }

    public function test_show_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.view');
        $branch = CommerceBranch::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $branch->id]);
    }

    public function test_delete_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.delete');
        $branch = CommerceBranch::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertNoContent();
        $this->assertSoftDeleted('commerce_branches', ['id' => $branch->id]);
    }

    public function test_index_by_commerce(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.view');
        $commerce = Commerce::factory()->create();
        CommerceBranch::factory()->count(2)->create(['commerce_id' => $commerce->id]);
        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$commerce->id}/branches")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }
}
