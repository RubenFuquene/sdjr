<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

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
                'message' => __('throttle.too_many_requests'),
                'code' => 429,
            ]);
        $response->assertHeader('Retry-After');
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    /**
     * SCRUM-354: el mensaje de 429 estaba hardcodeado en inglés, sin pasar
     * por i18n, aunque SetLocale ya resuelve el idioma de la request. Esta
     * prueba fija el comportamiento real reportado: un cliente que manda
     * Accept-Language: es debe recibir el mensaje en español, no en inglés.
     */
    public function test_login_throttle_message_respects_accept_language_header(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->withHeaders(['Accept-Language' => 'es-CO,es;q=0.9'])
                ->postJson('/api/v1/login', $payload);
        }
        $response = $this->withHeaders(['Accept-Language' => 'es-CO,es;q=0.9'])
            ->postJson('/api/v1/login', $payload);

        $response->assertStatus(429)
            ->assertJson([
                'status' => false,
                'message' => 'Demasiadas solicitudes. Por favor intenta de nuevo más tarde.',
                'code' => 429,
            ]);
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
                'message' => __('throttle.too_many_requests'),
                'code' => 429,
            ]);
        $response->assertHeader('Retry-After');
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }
}
