<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\City;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Department;
use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\MockS3DiskTrait;

class CommerceBranchTest extends TestCase
{
    use MockS3DiskTrait, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.branches.create', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.branches.update', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.branches.show', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.branches.delete', 'guard_name' => 'sanctum']);
        $this->setUpMockS3Disk();
    }

    public function test_create_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.create');
        // SCRUM-287: el commerce_id del payload ahora se valida contra el
        // dueño autenticado (antes cualquier comercio pasaba) — el comercio
        // debe ser del usuario que crea la sucursal.
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $department = Department::factory()->create();
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create();
        $payload = [
            'commerce_branch' => [
                'commerce_id' => $commerce->id,
                'department_id' => $department->id,
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'name' => 'Sucursal Test',
                'address' => 'Calle 123',
                'latitude' => 4.6,
                'longitude' => -74.1,
                'phone' => '3001234567',
                'email' => 'sucursal@test.com',
                'status' => true,
            ],
            'commerce_branch_hours' => [
                [
                    'day_of_week' => 1,
                    'open_time' => '08:00',
                    'close_time' => '18:00',
                    'note' => 'Horario normal',
                ],
                [
                    'day_of_week' => 2,
                    'open_time' => '09:00',
                    'close_time' => '15:00',
                    'note' => 'Horario reducido',
                ],
            ],
            'commerce_branch_photos' => [
                [
                    'file_name' => 'branch_photo.jpg',
                    'mime_type' => 'png',
                    'file_size_bytes' => 45000,
                    'versioning_enabled' => 'false',
                    'metadata' => ['description' => 'Foto de la sucursal'],
                ],
                [
                    'file_name' => 'branch_photo.jpg',
                    'mime_type' => 'jpeg',
                    'file_size_bytes' => 45000,
                    'versioning_enabled' => 'false',
                    'metadata' => ['description' => 'Foto de la sucursal'],
                ],
            ],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/commerce-branches', $payload)
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Sucursal test']);

        $branchId = $response->json('data.id');
        $this->assertDatabaseHas('commerce_branch_hours', [
            'commerce_branch_id' => $branchId,
            'day_of_week' => 1,
            'open_time' => '08:00',
            'close_time' => '18:00',
        ]);
        $this->assertDatabaseHas('commerce_branch_hours', [
            'commerce_branch_id' => $branchId,
            'day_of_week' => 2,
            'open_time' => '09:00',
            'close_time' => '15:00',
        ]);
    }

    public function test_update_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.update');

        $commerce = Commerce::factory()->create([
            'owner_user_id' => $user->id,
        ]);

        $branch = CommerceBranch::factory()->create([
            'commerce_id' => $commerce->id,
        ]);

        $payload = [
            'commerce_branch' => [
                'commerce_id' => $commerce->id,
                'name' => 'Sucursal Editada',
                'address' => 'Calle 456 #78-90',
                'phone' => '3009876543',
                'status' => true,
            ],
            'commerce_branch_hours' => [
                [
                    'day_of_week' => 1,
                    'open_time' => '08:00',
                    'close_time' => '18:00',
                    'note' => 'Horario actualizado',
                ],
            ],
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/commerce-branches/{$branch->id}", $payload)
            ->assertOk()
            ->assertJsonFragment(['name' => 'Sucursal editada']);
    }

    public function test_show_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.show');
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $branch->id]);
    }

    /**
     * SCRUM-334: authorize() solo validaba el permiso, nunca la propiedad —
     * cualquier aliado podía ver la sucursal de un comercio ajeno.
     */
    public function test_show_branch_fails_for_a_branch_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.show');
        $branch = CommerceBranch::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertForbidden();
    }

    public function test_delete_branch_success(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.delete');
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertNoContent();
        $this->assertSoftDeleted('commerce_branches', ['id' => $branch->id]);
    }

    /**
     * SCRUM-334: la más severa de las tres — permitía BORRAR la sucursal de
     * un comercio ajeno, no solo verla o moverla.
     */
    public function test_delete_branch_fails_for_a_branch_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.delete');
        $branch = CommerceBranch::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertForbidden();
        $this->assertDatabaseHas('commerce_branches', ['id' => $branch->id, 'deleted_at' => null]);
    }

    /**
     * Superadmin conserva acceso a sucursales de cualquier comercio.
     */
    public function test_superadmin_can_show_and_delete_any_commerce_branch(): void
    {
        \Spatie\Permission\Models\Role::findOrCreate('superadmin', 'sanctum');
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        $admin->givePermissionTo(['provider.branches.show', 'provider.branches.delete']);
        $branch = CommerceBranch::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/commerce-branches/{$branch->id}")
            ->assertNoContent();
    }

    /**
     * SCRUM-287: antes, el commerce_id del payload solo se validaba con
     * exists:commerces,id — cualquier comercio del sistema pasaba. Un aliado
     * podía crear una sucursal DENTRO del comercio de otro.
     */
    public function test_create_branch_rejects_a_commerce_id_not_owned_by_the_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.create');
        $otherOwnersCommerce = Commerce::factory()->create();
        $department = Department::factory()->create();
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create();

        $payload = [
            'commerce_branch' => [
                'commerce_id' => $otherOwnersCommerce->id,
                'department_id' => $department->id,
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'name' => 'Sucursal Intrusa',
                'address' => 'Calle 123',
                'status' => true,
            ],
            'commerce_branch_hours' => [
                ['day_of_week' => 1, 'open_time' => '08:00', 'close_time' => '18:00'],
            ],
            'commerce_branch_photos' => [],
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/commerce-branches', $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('commerce_branches', ['name' => 'Sucursal Intrusa']);
    }

    /**
     * Superadmin sigue pudiendo crear sucursales en cualquier comercio — la
     * validación de propiedad no debe alcanzarlo (mismo trait que ya usa
     * StoreDocumentUploadRequest).
     */
    public function test_create_branch_allows_superadmin_for_any_commerce(): void
    {
        \Spatie\Permission\Models\Role::findOrCreate('superadmin', 'sanctum');
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        // El permiso se exige siempre, incluso a superadmin — igual que en
        // UpdateCommerceBranchRequest::authorize(). Solo la PROPIEDAD del
        // comercio se exime por rol (vía AuthorizesCommerceOwnership).
        $admin->givePermissionTo('provider.branches.create');
        $commerce = Commerce::factory()->create();
        $department = Department::factory()->create();
        $city = City::factory()->create();
        $neighborhood = Neighborhood::factory()->create();

        $payload = [
            'commerce_branch' => [
                'commerce_id' => $commerce->id,
                'department_id' => $department->id,
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'name' => 'Sucursal Admin',
                'address' => 'Calle 123',
                'status' => true,
            ],
            'commerce_branch_hours' => [
                ['day_of_week' => 1, 'open_time' => '08:00', 'close_time' => '18:00'],
            ],
            'commerce_branch_photos' => [],
        ];

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/commerce-branches', $payload)
            ->assertCreated();
    }

    /**
     * SCRUM-287: commerce_id ya no se acepta al editar — antes, como
     * quedaba en el fillable del modelo, un update() masivo permitía mover
     * una sucursal propia al comercio de un tercero.
     */
    public function test_update_branch_ignores_an_attempt_to_reassign_the_commerce(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.update');
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $otherCommerce = Commerce::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $ownCommerce->id]);

        $payload = [
            'commerce_branch' => [
                'commerce_id' => $otherCommerce->id,
                'name' => 'Sucursal Reasignada',
                'status' => true,
            ],
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/commerce-branches/{$branch->id}", $payload)
            ->assertOk();

        $this->assertDatabaseHas('commerce_branches', [
            'id' => $branch->id,
            'commerce_id' => $ownCommerce->id,
            'name' => 'Sucursal reasignada',
        ]);
    }

    /**
     * SCRUM-334: GET /commerce-branches (listado general) solo debe devolver
     * las sucursales de los comercios del usuario autenticado.
     */
    public function test_index_excludes_branches_from_commerces_not_owned_by_user(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.show');
        $ownCommerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        CommerceBranch::factory()->create(['commerce_id' => $ownCommerce->id]);
        CommerceBranch::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/commerce-branches')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_by_commerce(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.branches.show');
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        CommerceBranch::factory()->count(2)->create(['commerce_id' => $commerce->id]);
        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/commerces/{$commerce->id}/branches")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }
}
