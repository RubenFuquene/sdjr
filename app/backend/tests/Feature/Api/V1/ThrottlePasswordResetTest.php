<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThrottlePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test password forgot endpoint throttling (should return 429 after 10 requests/min).
     */
    public function test_password_forgot_throttle_returns_429_after_limit(): void
    {
        $payload = [
            'email' => Str::random(10).'@example.com',
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/password/forgot', $payload);
        }
        $response = $this->postJson('/api/v1/password/forgot', $payload);
        $response->assertStatus(429)
            ->assertJson([
                'status' => false,
                'message' => 'Too many requests. Please try again later.',
                'code' => 429,
            ]);
        $response->assertHeader('Retry-After');
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    /**
     * Test password reset endpoint throttling (should return 429 after 10 requests/min).
     */
    public function test_password_reset_throttle_returns_429_after_limit(): void
    {
        $payload = [
            'email' => Str::random(10).'@example.com',
            'token' => Str::random(32),
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/password/reset', $payload);
        }
        $response = $this->postJson('/api/v1/password/reset', $payload);
        $response->assertStatus(429)
            ->assertJson([
                'status' => false,
                'message' => 'Too many requests. Please try again later.',
                'code' => 429,
            ]);
        $response->assertHeader('Retry-After');
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }
}
