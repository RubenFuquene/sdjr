<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\City;
use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NeighborhoodTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que el endpoint index retorna los barrios correctamente.
     */
    public function test_index_returns_neighborhoods(): void
    {
        Permission::firstOrCreate(['name' => 'admin.params.neighborhoods.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.neighborhoods.index');
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        Neighborhood::factory()->create(['city_id' => $city->id, 'name' => 'Chapinero', 'code' => 'NB0001']);
        $response = $this->getJson('/api/v1/neighborhoods');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Chapinero')
            ->assertJsonPath('data.0.code', 'NB0001');
    }

    /**
     * Prueba que el endpoint index filtra barrios por ciudad (city_id).
     */
    public function test_index_filters_neighborhoods_by_city_id(): void
    {
        Permission::firstOrCreate(['name' => 'admin.params.neighborhoods.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.neighborhoods.index');
        Sanctum::actingAs($user);
        $bogota = City::factory()->create(['name' => 'Bogotá', 'code' => 'CT0001']);
        $medellin = City::factory()->create(['name' => 'Medellín', 'code' => 'CT0002']);
        Neighborhood::factory()->create(['city_id' => $bogota->id, 'name' => 'Chapinero', 'code' => 'NB0010']);
        Neighborhood::factory()->create(['city_id' => $bogota->id, 'name' => 'Usaquén', 'code' => 'NB0011']);
        Neighborhood::factory()->create(['city_id' => $medellin->id, 'name' => 'El Poblado', 'code' => 'NB0012']);

        $response = $this->getJson("/api/v1/neighborhoods?city_id={$bogota->id}&per_page=100");

        $response->assertStatus(200)->assertJsonCount(2, 'data');
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Chapinero'));
        $this->assertTrue($names->contains('Usaquén'));
        $this->assertFalse($names->contains('El Poblado'));
    }

    /**
     * Prueba que el filtro city_id, combinado con un per_page elevado, permite
     * traer el catálogo completo de barrios de Bogotá (1.091) sin truncar a 100.
     */
    public function test_index_returns_full_bogota_catalog_without_truncation(): void
    {
        $this->seed([
            \Database\Seeders\CountrySeeder::class,
            \Database\Seeders\DepartmentSeeder::class,
            \Database\Seeders\CitySeeder::class,
            \Database\Seeders\NeighborhoodSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'admin.params.neighborhoods.index', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.neighborhoods.index');
        Sanctum::actingAs($user);

        $bogota = City::where('code', '11001')->firstOrFail();

        $response = $this->getJson("/api/v1/neighborhoods?city_id={$bogota->id}&per_page=2000");

        $response->assertStatus(200)
            ->assertJsonCount(1091, 'data')
            ->assertJsonPath('meta.total', 1091);
    }

    /**
     * Prueba que el endpoint store crea un barrio correctamente.
     */
    public function test_store_creates_neighborhood(): void
    {
        Permission::firstOrCreate(['name' => 'admin.params.neighborhoods.create', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.neighborhoods.create');
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        $data = [
            'city_id' => $city->id,
            'name' => 'La Soledad',
            'code' => 'NB0002',
            'status' => Constant::STATUS_ACTIVE,
        ];
        $response = $this->postJson('/api/v1/neighborhoods', $data);
        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'La Soledad')
            ->assertJsonPath('data.code', 'NB0002');
        $this->assertDatabaseHas('neighborhoods', [
            'city_id' => $city->id,
            'name' => 'La Soledad',
            'code' => 'NB0002',
            'status' => $data['status'],
        ]);
    }

    /**
     * Prueba que el endpoint show retorna el detalle de un barrio correctamente.
     */
    public function test_show_returns_neighborhood(): void
    {
        Permission::firstOrCreate(['name' => 'admin.params.neighborhoods.show', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.neighborhoods.show');
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id, 'name' => 'Teusaquillo', 'code' => 'NB0003']);
        $response = $this->getJson("/api/v1/neighborhoods/{$neighborhood->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Teusaquillo')
            ->assertJsonPath('data.code', 'NB0003');
    }

    /**
     * Prueba que el endpoint update actualiza un barrio correctamente.
     */
    public function test_update_updates_neighborhood(): void
    {
        Permission::firstOrCreate(['name' => 'admin.params.neighborhoods.update', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.neighborhoods.update');
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
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'San Luis Updated')
            ->assertJsonPath('data.code', 'NB0005');
        $this->assertDatabaseHas('neighborhoods', [
            'city_id' => $city->id,
            'name' => 'San Luis Updated',
            'code' => 'NB0005',
            'status' => $data['status'],
        ]);
    }

    /**
     * Prueba que el endpoint destroy elimina (soft delete) un barrio correctamente.
     */
    public function test_destroy_deletes_neighborhood(): void
    {
        Permission::firstOrCreate(['name' => 'admin.params.neighborhoods.delete', 'guard_name' => 'sanctum']);
        $user = User::factory()->create();
        $user->givePermissionTo('admin.params.neighborhoods.delete');
        Sanctum::actingAs($user);
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id, 'name' => 'El Lago', 'code' => 'NB0006']);
        $response = $this->deleteJson("/api/v1/neighborhoods/{$neighborhood->id}");
        $response->assertStatus(204);
        $this->assertSoftDeleted('neighborhoods', ['id' => $neighborhood->id]);
    }
}
