<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\PriorityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PriorityTypeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_priority_types()
    {
        Permission::firstOrCreate(['name' => 'admin.priority_types.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.priority_types.index');
        Sanctum::actingAs($user);

        PriorityType::factory()->count(2)->create();
        $response = $this->getJson('/api/v1/priority-types');
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_store_creates_priority_type()
    {
        Permission::firstOrCreate(['name' => 'admin.priority_types.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.priority_types.create');
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Alta',
            'code' => 'HIGH',
            'status' => Constant::STATUS_ACTIVE,
        ];
        $response = $this->postJson('/api/v1/priority-types', $data);
        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Alta')
            ->assertJsonPath('data.code', 'HIGH');

        $this->assertDatabaseHas('priority_types', [
            'name' => 'Alta',
            'code' => 'HIGH',
            'status' => Constant::STATUS_ACTIVE,
        ]);
    }

    public function test_show_returns_priority_type()
    {
        Permission::firstOrCreate(['name' => 'admin.priority_types.show', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.priority_types.show');
        Sanctum::actingAs($user);

        $priorityType = PriorityType::factory()->create(['name' => 'Alta', 'code' => 'HIGH']);
        $response = $this->getJson('/api/v1/priority-types/'.$priorityType->id);
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Alta')
            ->assertJsonPath('data.code', 'HIGH');
    }

    public function test_update_updates_priority_type()
    {
        Permission::firstOrCreate(['name' => 'admin.priority_types.update', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.priority_types.update');
        Sanctum::actingAs($user);

        $priorityType = PriorityType::factory()->create(['name' => 'Alta', 'code' => 'HIGH']);
        $data = ['name' => 'Media'];
        $response = $this->putJson('/api/v1/priority-types/'.$priorityType->id, $data);
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Media']);

        $this->assertDatabaseHas('priority_types', ['name' => 'Media']);
    }

    public function test_destroy_deletes_priority_type()
    {
        Permission::firstOrCreate(['name' => 'admin.priority_types.delete', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.priority_types.delete');
        Sanctum::actingAs($user);

        $priorityType = PriorityType::factory()->create();
        $response = $this->deleteJson('/api/v1/priority-types/'.$priorityType->id);
        $response->assertStatus(204);
    }

    public function test_store_priority_type_validation_error()
    {
        Permission::firstOrCreate(['name' => 'admin.priority_types.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.priority_types.create');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/priority-types', []);
        $response->assertStatus(422);
    }

    /**
     * Test that unauthenticated users cannot access the endpoints.
     */
    public function test_unauthenticated_user_cannot_access()
    {
        $response = $this->getJson('/api/v1/priority-types');
        $response->assertStatus(401);
    }
}
