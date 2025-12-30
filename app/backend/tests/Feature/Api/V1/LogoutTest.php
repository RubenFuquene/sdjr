<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que un usuario autenticado puede cerrar sesión correctamente.
     */
    public function test_it_logs_out_authenticated_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertOk()
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);
    }

    
    /**
     * Prueba que se requiere autenticación para cerrar sesión.
     */
    public function test_it_requires_authentication()
    {
        $response = $this->postJson('/api/v1/logout');
        $response->assertUnauthorized();
    }
}
