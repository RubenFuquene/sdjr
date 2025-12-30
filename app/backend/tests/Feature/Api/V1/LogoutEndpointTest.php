<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_out_authenticated_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertOk()
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->postJson('/api/v1/logout');
        $response->assertUnauthorized();
    }
}
