<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ThrottleAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user throttle (should return 429 after 100 requests/min).
     */
    public function test_authenticated_throttle_returns_429_after_limit(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        for ($i = 0; $i < 100; $i++) {
            $this->getJson('/api/v1/me');
        }
        $response = $this->getJson('/api/v1/me');
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
