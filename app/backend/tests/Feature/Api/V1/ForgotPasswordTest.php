<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $authUser = User::factory()->create();
        $this->actingAs($authUser, 'sanctum');

        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Recovery email sent successfully.'
            ]);
    }
    
    public function test_cannot_request_reset_with_invalid_email(): void
    {
        $authUser = User::factory()->create();
        $this->actingAs($authUser, 'sanctum');

        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'notfound@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

}
