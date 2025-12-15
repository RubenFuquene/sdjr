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
use Spatie\Permission\Models\Permission;

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
        Permission::firstOrCreate(['name' => 'departments.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('departments.index');
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
        Permission::firstOrCreate(['name' => 'departments.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('departments.create');
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
            ->assertJsonPath('data.name', 'Antioquia')
            ->assertJsonPath('data.code', 'DEP002');

        $this->assertDatabaseHas('departments', [
            'name' => 'Antioquia',
            'code' => 'DEP002',
            'country_id' => $country->id,
            'status' => $data['status'],
        ]);
    }

    /**
     * Test that the show endpoint returns a specific department.
     *
     * @return void
     */
    public function test_show_returns_department()
    {
        Permission::firstOrCreate(['name' => 'departments.show', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('departments.show');
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
            ->assertJsonPath('data.name', 'Valle del cauca')
            ->assertJsonPath('data.code', 'DEP003');
    }

    /**
     * Test that the update endpoint updates an existing department.
     *
     * @return void
     */
    public function test_update_updates_department()
    {
        Permission::firstOrCreate(['name' => 'departments.update', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('departments.update');
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
        Permission::firstOrCreate(['name' => 'departments.delete', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('departments.delete');
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

        $response->assertStatus(204);

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
