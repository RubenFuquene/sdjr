<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleUpdateEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_a_role()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $role = Role::create(['name' => 'editor', 'description' => 'Edit role']);
        $payload = [
            'name' => 'editor-updated',
            'description' => 'Updated description',
            'permissions' => []
        ];
        $response = $this->putJson('/api/v1/roles/' . $role->id, $payload);
        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'editor-updated',
                'description' => 'Updated description',
                'message' => 'Role updated successfully',
                'status' => true,
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_role_on_update()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/roles/9999', [
            'name' => 'notfound',
            'description' => 'notfound',
            'permissions' => []
        ]);
        $response->assertStatus(404)
            ->assertJsonFragment([
                'message' => 'Role not found',
                'status' => false,
            ]);
    }
}
