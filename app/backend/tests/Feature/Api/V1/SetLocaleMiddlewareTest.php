<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetLocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_defaults_to_spanish_without_accept_language_header(): void
    {
        // Symfony\Component\HttpFoundation\Request::create() (usado por postJson) inyecta
        // por defecto "Accept-Language: en-us,en;q=0.5" simulando un navegador — se
        // sobrescribe con vacío para simular una request real sin la cabecera.
        $response = $this->withHeaders(['Accept-Language' => ''])
            ->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'El campo correo electrónico es obligatorio.');
    }

    public function test_validation_error_is_returned_in_english_when_requested(): void
    {
        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'The email field is required.');
    }

    public function test_unsupported_locale_falls_back_to_default_spanish(): void
    {
        $response = $this->withHeaders(['Accept-Language' => 'fr'])
            ->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'El campo correo electrónico es obligatorio.');
    }
}
