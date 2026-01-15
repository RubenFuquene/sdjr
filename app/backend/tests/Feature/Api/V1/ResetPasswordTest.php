<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $authUser = User::factory()->create();
        $this->actingAs($authUser, 'sanctum');
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/password/reset', [
            'email' => 'reset@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Password reset successfully.'
            ]);
    }

    public function test_cannot_reset_password_with_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset2@example.com']);
        $authUser = User::factory()->create();
        $this->actingAs($authUser, 'sanctum');

        $response = $this->postJson('/api/v1/password/reset', [
            'email' => 'reset2@example.com',
            'token' => 'invalidtoken',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'status' => false,
                'message' => 'Could not reset password.'
            ]);
    }

}
