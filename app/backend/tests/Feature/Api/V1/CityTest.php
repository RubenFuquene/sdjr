<?php

namespace Tests\Feature\Api\V1;

use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Faker\Generator;
use App\Constants\Constant;

class CityTest extends TestCase
{
    use RefreshDatabase;
    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Test that the index endpoint returns a list of cities.
     *
     * @return void
     */
    public function test_index_returns_cities()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create(['name' => 'Colombia', 'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])]);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Cundinamarca', 'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])]);
        City::create(['department_id' => $department->id, 'name' => 'Bogota', 'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])]);

        $response = $this->getJson('/api/v1/cities');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test that the store endpoint creates a new city.
     *
     * @return void
     */
    public function test_store_creates_city()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create(['name' => 'Colombia', 'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])]);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Antioquia', 'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])]);
        $data = ['department_id' => $department->id, 'name' => 'Medellin', 'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])];

        $response = $this->postJson('/api/v1/cities', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Medellin']);

        $this->assertDatabaseHas('cities', ['name' => 'Medellin']);
    }

    /**
     * Test that the show endpoint returns a specific city.
     *
     * @return void
     */
    public function test_show_returns_city()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create(['name' => 'Colombia', 'status' => Constant::STATUS_ACTIVE]);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Valle del Cauca', 'status' => Constant::STATUS_ACTIVE]);
        $city = City::create(['department_id' => $department->id, 'name' => 'Cali', 'status' => Constant::STATUS_ACTIVE]);

        $response = $this->getJson("/api/v1/cities/{$city->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Cali']);
    }

    /**
     * Test that the update endpoint updates an existing city.
     *
     * @return void
     */
    public function test_update_updates_city()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create(['name' => 'Colombia', 'status' => Constant::STATUS_ACTIVE]);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Atlantico', 'status' => Constant::STATUS_ACTIVE]);
        $city = City::create(['department_id' => $department->id, 'name' => 'Barranquilla', 'status' => Constant::STATUS_ACTIVE]);
        $data = ['department_id' => $department->id, 'name' => 'Barranquilla Updated', 'status' => Constant::STATUS_INACTIVE];

        $response = $this->putJson("/api/v1/cities/{$city->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Barranquilla Updated']);

        $this->assertDatabaseHas('cities', ['name' => 'Barranquilla Updated']);
    }

    /**
     * Test that the destroy endpoint deletes a city.
     *
     * @return void
     */
    public function test_destroy_deletes_city()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create(['name' => 'Colombia', 'status' => Constant::STATUS_ACTIVE]);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Bolivar', 'status' => Constant::STATUS_ACTIVE]);
        $city = City::create(['department_id' => $department->id, 'name' => 'Cartagena', 'status' => Constant::STATUS_ACTIVE]);

        $response = $this->deleteJson("/api/v1/cities/{$city->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    /**
     * Test that unauthenticated users cannot access the endpoints.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access()
    {
        $response = $this->getJson('/api/v1/cities');

        $response->assertStatus(401);
    }
}
