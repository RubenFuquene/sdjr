<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdministratorsEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_administrator_users()
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/administrators');

        $response->assertOk()
            ->assertJsonFragment(['id' => $admin->id])
            ->assertJsonMissing(['id' => $user->id]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/administrators');
        $response->assertUnauthorized();
    }
}
