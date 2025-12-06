<?php

namespace Tests\Feature\Api\V1;

use App\Models\Country;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the index endpoint returns a list of departments.
     *
     * @return void
     */
    public function test_index_returns_departments()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create(['name' => 'Colombia', 'status' => 'A']);
        Department::create(['country_id' => $country->id, 'name' => 'Cundinamarca', 'status' => 'A']);

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

        $country = Country::create(['name' => 'Colombia', 'status' => 'A']);
        $data = ['country_id' => $country->id, 'name' => 'Antioquia', 'status' => 'A'];

        $response = $this->postJson('/api/v1/departments', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Antioquia']);

        $this->assertDatabaseHas('departments', ['name' => 'Antioquia']);
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

        $country = Country::create(['name' => 'Colombia', 'status' => 'A']);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Valle del Cauca', 'status' => 'A']);

        $response = $this->getJson("/api/v1/departments/{$department->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Valle del Cauca']);
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

        $country = Country::create(['name' => 'Colombia', 'status' => 'A']);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Atlantico', 'status' => 'A']);
        $data = ['country_id' => $country->id, 'name' => 'Atlántico', 'status' => 'I'];

        $response = $this->putJson("/api/v1/departments/{$department->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Atlántico']);

        $this->assertDatabaseHas('departments', ['name' => 'Atlántico']);
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

        $country = Country::create(['name' => 'Colombia', 'status' => 'A']);
        $department = Department::create(['country_id' => $country->id, 'name' => 'Bolivar', 'status' => 'A']);

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
