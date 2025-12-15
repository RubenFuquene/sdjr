<?php

namespace Tests\Feature\Api\V1;

use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Faker\Generator;
use App\Constants\Constant;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;
    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Test that the index endpoint returns a list of departments.
     *
     * @return void
     */
    public function test_index_returns_departments()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])
        ]);
        Department::create([
            'country_id' => $country->id,
            'name' => 'Cundinamarca',
            'code' => 'DEP001',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])
        ]);

        $response = $this->getJson('/api/v1/departments');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test that the store endpoint creates a new department.
     *
     * @return void
     */
    public function test_store_creates_department()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])
        ]);
        $data = [
            'country_id' => $country->id,
            'name' => 'Antioquia',
            'code' => 'DEP002',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE])
        ];

        $response = $this->postJson('/api/v1/departments', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Antioquia', 'code' => 'DEP002']);

        $this->assertDatabaseHas('departments', ['name' => 'Antioquia', 'code' => 'DEP002']);
    }

    /**
     * Test that the show endpoint returns a specific department.
     *
     * @return void
     */
    public function test_show_returns_department()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => Constant::STATUS_ACTIVE
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Valle del Cauca',
            'code' => 'DEP003',
            'status' => Constant::STATUS_ACTIVE
        ]);

        $response = $this->getJson("/api/v1/departments/{$department->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Valle del Cauca', 'code' => 'DEP003']);
    }

    /**
     * Test that the update endpoint updates an existing department.
     *
     * @return void
     */
    public function test_update_updates_department()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => Constant::STATUS_ACTIVE
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Atlantico',
            'code' => 'DEP004',
            'status' => Constant::STATUS_ACTIVE
        ]);
        $data = [
            'country_id' => $country->id,
            'name' => 'Atlántico',
            'code' => 'DEP005',
            'status' => Constant::STATUS_INACTIVE
        ];

        $response = $this->putJson("/api/v1/departments/{$department->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Atlántico', 'code' => 'DEP005']);

        $this->assertDatabaseHas('departments', ['name' => 'Atlántico', 'code' => 'DEP005']);
    }

    /**
     * Test that the destroy endpoint deletes a department.
     *
     * @return void
     */
    public function test_destroy_deletes_department()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => Constant::STATUS_ACTIVE
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Bolivar',
            'code' => 'DEP006',
            'status' => Constant::STATUS_ACTIVE
        ]);

        $response = $this->deleteJson("/api/v1/departments/{$department->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    /**
     * Test that unauthenticated users cannot access the endpoints.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access()
    {
        $response = $this->getJson('/api/v1/departments');

        $response->assertStatus(401);
    }
}
