<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ThrottleOrderCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test order creation endpoint throttling (should return 429 after 100 requests/min).
     */
    public function test_order_create_throttle_returns_429_after_limit(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = [
            // Completa con los campos mínimos requeridos por Order
        ];
        for ($i = 0; $i < 100; $i++) {
            $this->postJson('/api/v1/orders', $payload);
        }
        $response = $this->postJson('/api/v1/orders', $payload);
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
