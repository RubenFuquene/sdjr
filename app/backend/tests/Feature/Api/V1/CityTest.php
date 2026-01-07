<?php

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

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
     * Verifica que el endpoint index retorne la lista de ciudades correctamente.
     *
     * Crea un usuario con permiso, una ciudad y valida que la respuesta contenga la ciudad creada.
     */
    public function test_index_returns_cities()
    {
        Permission::firstOrCreate(['name' => 'admin.params.cities.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.cities.index');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Cundinamarca',
            'code' => 'DEP001',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ]);
        City::create([
            'department_id' => $department->id,
            'name' => 'Bogota',
            'code' => 'CITY01',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ]);

        $response = $this->getJson('/api/v1/cities');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Verifica que el endpoint store cree una nueva ciudad correctamente.
     *
     * Crea un usuario con permiso, un departamento y envía los datos para crear una ciudad, validando la respuesta y la base de datos.
     */
    public function test_store_creates_city()
    {
        Permission::firstOrCreate(['name' => 'admin.params.cities.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.cities.create');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Antioquia',
            'code' => 'DEP002',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ]);
        $data = [
            'department_id' => $department->id,
            'name' => 'Medellin',
            'code' => 'CITY02',
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ];

        $response = $this->postJson('/api/v1/cities', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Medellin')
            ->assertJsonPath('data.code', 'CITY02');

        $this->assertDatabaseHas('cities', [
            'name' => 'Medellin',
            'code' => 'CITY02',
            'department_id' => $department->id,
            'status' => $data['status'],
        ]);
    }

    /**
     * Verifica que el endpoint show retorne la información de una ciudad específica.
     *
     * Crea una ciudad y valida que la respuesta contenga los datos correctos.
     */
    public function test_show_returns_city()
    {
        Permission::firstOrCreate(['name' => 'admin.params.cities.show', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.cities.show');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Valle del Cauca',
            'code' => 'DEP003',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $city = City::create([
            'department_id' => $department->id,
            'name' => 'Cali',
            'code' => 'CITY03',
            'status' => Constant::STATUS_ACTIVE,
        ]);

        $response = $this->getJson("/api/v1/cities/{$city->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Cali', 'code' => 'CITY03']);
    }

    /**
     * Verifica que el endpoint update actualice correctamente una ciudad existente.
     *
     * Crea una ciudad, envía datos de actualización y valida la respuesta y la base de datos.
     */
    public function test_update_updates_city()
    {
        Permission::firstOrCreate(['name' => 'admin.params.cities.update', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.cities.update');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Atlantico',
            'code' => 'DEP004',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $city = City::create([
            'department_id' => $department->id,
            'name' => 'Barranquilla',
            'code' => 'CITY04',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $data = [
            'department_id' => $department->id,
            'name' => 'Barranquilla Updated',
            'code' => 'CITY05',
            'status' => Constant::STATUS_INACTIVE,
        ];

        $response = $this->putJson("/api/v1/cities/{$city->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Barranquilla updated')
            ->assertJsonPath('data.code', 'CITY05');

        $this->assertDatabaseHas('cities', [
            'name' => 'Barranquilla updated',
            'code' => 'CITY05',
            'department_id' => $department->id,
            'status' => $data['status'],
        ]);
    }

    /**
     * Verifica que el endpoint destroy elimine correctamente una ciudad.
     *
     * Crea una ciudad, la elimina y valida que ya no exista en la base de datos.
     */
    public function test_destroy_deletes_city()
    {
        Permission::firstOrCreate(['name' => 'admin.params.cities.delete', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.cities.delete');
        Sanctum::actingAs($user);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO1234',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $department = Department::create([
            'country_id' => $country->id,
            'name' => 'Bolivar',
            'code' => 'DEP006',
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $city = City::create([
            'department_id' => $department->id,
            'name' => 'Cartagena',
            'code' => 'CITY06',
            'status' => Constant::STATUS_ACTIVE,
        ]);

        $response = $this->deleteJson("/api/v1/cities/{$city->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
    }

    /**
     * Verifica que un usuario no autenticado no pueda acceder a los endpoints de ciudades.
     *
     * Intenta acceder al endpoint index sin autenticación y espera un 401.
     */
    public function test_unauthenticated_user_cannot_access()
    {
        $response = $this->getJson('/api/v1/cities');

        $response->assertStatus(401);
    }
}
