<?php

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Country;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Test that the index endpoint returns a list of countries.
     *
     * @return void
     */
    public function test_index_returns_countries()
    {
        Permission::firstOrCreate(['name' => 'admin.countries.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.countries.index');
        Sanctum::actingAs($user);

        Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ]);
        Country::create([
            'name' => 'Peru',
            'code' => 'PE5678',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ]);

        $response = $this->getJson('/api/v1/countries');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test that the store endpoint creates a new country.
     *
     * @return void
     */
    public function test_store_creates_country()
    {
        Permission::firstOrCreate(['name' => 'admin.countries.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.countries.create');
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Argentina',
            'code' => 'AR0001',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ];

        $response = $this->postJson('/api/v1/countries', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Argentina')
            ->assertJsonPath('data.code', 'AR0001');

        $this->assertDatabaseHas('countries', [
            'name' => 'Argentina',
            'code' => 'AR0001',
            'status' => $data['status'],
        ]);
    }

    /**
     * Test that the show endpoint returns a specific country.
     *
     * @return void
     */
    public function test_show_returns_country()
    {
        Permission::firstOrCreate(['name' => 'admin.countries.show', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.countries.show');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Chile',
            'code' => 'CL9999',
            'status' => Constant::STATUS_ACTIVE,
        ]);

        $response = $this->getJson("/api/v1/countries/{$country->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Chile')
            ->assertJsonPath('data.code', 'CL9999');
    }

    /**
     * Test that the update endpoint updates an existing country.
     *
     * @return void
     */
    public function test_update_updates_country()
    {
        Permission::firstOrCreate(['name' => 'admin.countries.update', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.countries.update');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Brazil',
            'code' => 'BR0001',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $data = [
            'name' => 'Brasil',
            'code' => 'BR0002',
            'status' => Constant::STATUS_INACTIVE,
        ];

        $response = $this->putJson("/api/v1/countries/{$country->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('countries', $data);
    }

    /**
     * Test that the destroy endpoint deletes a country.
     *
     * @return void
     */
    public function test_destroy_deletes_country()
    {
        Permission::firstOrCreate(['name' => 'admin.countries.delete', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.countries.delete');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Uruguay',
            'code' => 'UY0001',
            'status' => Constant::STATUS_ACTIVE,
        ]);

        $response = $this->deleteJson("/api/v1/countries/{$country->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }

    /**
     * Test that unauthenticated users cannot access the endpoints.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access()
    {
        $response = $this->getJson('/api/v1/countries');

        $response->assertStatus(401);
    }
}
