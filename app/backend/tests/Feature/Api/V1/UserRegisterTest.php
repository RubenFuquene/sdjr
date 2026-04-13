<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);
    }

    /**
     * Test provider registration returns token on success.
     */
    public function test_register_provider_returns_token(): void
    {
        $payload = [
            'name' => 'Proveedor',
            'last_name' => 'Test',
            'email' => 'proveedor'.Str::random(5).'@test.com',
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
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'name' => 'Proveedor',
            'last_name' => 'Test',
        ]);

        $this->assertTrue($user->hasRole('provider'));
    }

    /**
     * Test provider registration fails with invalid data.
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

    /**
     * Test registration succeeds even if notification dispatch fails.
     */
    public function test_register_provider_succeeds_when_welcome_email_dispatch_fails(): void
    {
        Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'sanctum']);

        Notification::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $payload = [
            'name' => 'Proveedor',
            'last_name' => 'SinCorreo',
            'email' => 'nocorreo'.Str::random(5).'@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/provider/register', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => trim(Str::lower($payload['email'])),
            'name' => 'Proveedor',
            'last_name' => 'Sincorreo',
        ]);
    }

    /**
     * Test customer registration returns token on success.
     */
    public function test_register_customer_returns_token(): void
    {
        $payload = [
            'name' => 'Cliente',
            'last_name' => 'Test',
            'email' => 'cliente'.Str::random(5).'@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/customer/register', $payload);

        $user = User::where('email', trim(Str::lower($payload['email'])))->first();
        if ($user) {
            $user->refresh();
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'name' => 'Cliente',
            'last_name' => 'Test',
        ]);

        $this->assertTrue($user->hasRole('user'));
    }

    /**
     * Test customer registration fails with invalid data.
     */
    public function test_register_customer_fails_with_invalid_data(): void
    {
        $payload = [
            'name' => '',
            'last_name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/v1/customer/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'last_name', 'email', 'password']);
    }

    /**
     * Test customer registration fails if email already exists.
     */
    public function test_register_customer_fails_if_email_exists(): void
    {
        User::factory()->create(['email' => 'existing-customer@test.com']);

        $payload = [
            'name' => 'Cliente Test',
            'last_name' => 'Test',
            'email' => 'existing-customer@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/customer/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test customer registration succeeds even if notification dispatch fails.
     */
    public function test_register_customer_succeeds_when_welcome_email_dispatch_fails(): void
    {
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        Notification::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $payload = [
            'name' => 'Cliente',
            'last_name' => 'SinCorreo',
            'email' => 'nocorreo-customer'.Str::random(5).'@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/customer/register', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => trim(Str::lower($payload['email'])),
            'name' => 'Cliente',
            'last_name' => 'Sincorreo',
        ]);
    }
}
