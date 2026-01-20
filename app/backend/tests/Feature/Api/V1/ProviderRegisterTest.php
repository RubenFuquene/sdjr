<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProviderRegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test provider registration returns token on success.
     */
    public function test_register_provider_returns_token(): void
    {        
        Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'sanctum']);

        $payload = [
            'name' => 'Proveedor',
            'last_name' => 'Test',
            'email' => 'proveedor' . Str::random(5) . '@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->postJson('/api/v1/provider/register', $payload);

        // Forzar refresco de usuario tras creación
        $user = User::where('email', trim(Str::lower($payload['email'])))->first();
        if ($user) {
            $user->refresh();
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'token'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'name' => 'Proveedor',
            'last_name' => 'Test',
        ]);

        $this->assertTrue($user->hasRole('provider'));
    }

    /**
     * Test registration fails with invalid data.
     */
    public function test_register_provider_fails_with_invalid_data(): void
    {
        $payload = [
            'name' => '',
            'last_name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/v1/provider/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'last_name', 'email', 'password']);
    }

    /**
     * Test registration fails if email already exists.
     */
    public function test_register_provider_fails_if_email_exists(): void
    {
        $user = User::factory()->create(['email' => 'existing@test.com']);

        $payload = [
            'name' => 'Proveedor Test',
            'last_name' => 'Test',
            'email' => 'existing@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/provider/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
