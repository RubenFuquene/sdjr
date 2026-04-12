<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThrottleCustomerRegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer register endpoint throttling (should return 429 after 10 requests/min).
     */
    public function test_customer_register_throttle_returns_429_after_limit(): void
    {
        $payload = [
            'name' => 'Test Customer',
            'email' => Str::random(10).'@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/customer/register', $payload);
        }
        $response = $this->postJson('/api/v1/customer/register', $payload);
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
