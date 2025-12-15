<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Neighborhood;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Constants\Constant;

class NeighborhoodTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_neighborhoods(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        Neighborhood::factory()->create(['city_id' => $city->id, 'name' => 'Chapinero', 'code' => 'NB0001']);
        $response = $this->getJson('/api/v1/neighborhoods');
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Chapinero', 'code' => 'NB0001']);
    }

    public function test_store_creates_neighborhood(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        $data = [
            'city_id' => $city->id,
            'name' => 'La Soledad',
            'code' => 'NB0002',
            'status' => Constant::STATUS_ACTIVE,
        ];
        $response = $this->postJson('/api/v1/neighborhoods', $data);
        $response->assertStatus(201)->assertJsonFragment(['name' => 'La Soledad', 'code' => 'NB0002']);
        $this->assertDatabaseHas('neighborhoods', $data);
    }

    public function test_show_returns_neighborhood(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id, 'name' => 'Teusaquillo', 'code' => 'NB0003']);
        $response = $this->getJson("/api/v1/neighborhoods/{$neighborhood->id}");
        $response->assertStatus(200)->assertJsonFragment(['name' => 'Teusaquillo', 'code' => 'NB0003']);
    }

    public function test_update_updates_neighborhood(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id, 'name' => 'San Luis', 'code' => 'NB0004']);
        $data = [
            'city_id' => $city->id,
            'name' => 'San Luis Updated',
            'code' => 'NB0005',
            'status' => Constant::STATUS_INACTIVE,
        ];
        $response = $this->putJson("/api/v1/neighborhoods/{$neighborhood->id}", $data);
        $response->assertStatus(200)->assertJsonFragment(['name' => 'San Luis Updated', 'code' => 'NB0005']);
        $this->assertDatabaseHas('neighborhoods', ['name' => 'San Luis Updated', 'code' => 'NB0005']);
    }

    public function test_destroy_deletes_neighborhood(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id, 'name' => 'El Lago', 'code' => 'NB0006']);
        $response = $this->deleteJson("/api/v1/neighborhoods/{$neighborhood->id}");
        $response->assertStatus(204);
        $this->assertSoftDeleted('neighborhoods', ['id' => $neighborhood->id]);
    }
}
