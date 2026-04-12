<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThrottleAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login endpoint throttling (should return 429 after 10 requests/min).
     */
    public function test_login_throttle_returns_429_after_limit(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/login', $payload);
        }
        $response = $this->postJson('/api/v1/login', $payload);
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
     * Test nearby branches endpoint throttling (should return 429 after 60 requests/min).
     */
    public function test_nearby_branches_throttle_returns_429_after_limit(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/v1/nearby/branches?lat=4.7&lng=-74.0');
        }
        $response = $this->getJson('/api/v1/nearby/branches?lat=4.7&lng=-74.0');
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
