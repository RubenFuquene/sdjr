<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\PqrsType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PqrsTypeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_pqrs_types_success(): void
    {
        Permission::create(['name' => 'admin.params.pqrs_types.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.pqrs_types.index');
        $this->actingAs($user, 'sanctum');
        PqrsType::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/pqrs-types');
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
        $json = $response->json();
        $this->assertTrue($json['status']);
        $this->assertIsArray($json['data']);
    }

    public function test_store_pqrs_type_success(): void
    {
        Permission::create(['name' => 'admin.params.pqrs_types.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.pqrs_types.create');
        $this->actingAs($user, 'sanctum');
        $payload = [
            'name' => 'Consulta',
            'code' => 'CONSULTA',
            'status' => Constant::STATUS_ACTIVE,
        ];
        $response = $this->postJson('/api/v1/pqrs-types', $payload);
        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'data']);
        $json = $response->json();
        $this->assertTrue($json['status']);
        $this->assertIsArray($json['data']);
    }

    public function test_store_pqrs_type_validation_error(): void
    {
        Permission::create(['name' => 'admin.params.pqrs_types.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.pqrs_types.create');
        $this->actingAs($user, 'sanctum');
        $payload = [
            'name' => '',
            'code' => '',
        ];
        $response = $this->postJson('/api/v1/pqrs-types', $payload);
        $response->assertStatus(422);
        $json = $response->json();
        $this->assertArrayHasKey('message', $json);
    }

    public function test_show_pqrs_type_success(): void
    {
        Permission::create(['name' => 'admin.params.pqrs_types.show', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.pqrs_types.show');
        $this->actingAs($user, 'sanctum');
        $pqrsType = PqrsType::factory()->create();
        $response = $this->getJson('/api/v1/pqrs-types/'.$pqrsType->id);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
        $json = $response->json();
        $this->assertTrue($json['status']);
        $this->assertIsArray($json['data']);
    }

    public function test_update_pqrs_type_success(): void
    {
        Permission::create(['name' => 'admin.params.pqrs_types.update', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.pqrs_types.update');
        $this->actingAs($user, 'sanctum');
        $pqrsType = PqrsType::factory()->create();
        $payload = [
            'name' => 'Petición',
            'code' => 'PETICION',
        ];
        $response = $this->putJson('/api/v1/pqrs-types/'.$pqrsType->id, $payload);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
        $json = $response->json();
        $this->assertTrue($json['status']);
        $this->assertIsArray($json['data']);
    }

    public function test_delete_pqrs_type_success(): void
    {
        Permission::create(['name' => 'admin.params.pqrs_types.delete', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.pqrs_types.delete');
        $this->actingAs($user, 'sanctum');
        $pqrsType = PqrsType::factory()->create();
        $response = $this->deleteJson('/api/v1/pqrs-types/'.$pqrsType->id);
        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    public function test_unauthorized_access(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/pqrs-types');
        $response->assertStatus(403);
        $json = $response->json();
        $this->assertArrayHasKey('message', $json);
    }
}
