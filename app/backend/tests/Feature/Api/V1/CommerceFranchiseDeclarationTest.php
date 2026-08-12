<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\EstablishmentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362/365: declaración de franquicia y establishment_type_id
 * obligatorio en los tres caminos de escritura del comercio.
 */
class CommerceFranchiseDeclarationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.commerces.create', 'sanctum');
        Permission::findOrCreate('provider.commerces.update', 'sanctum');
    }

    private function actingAsProvider(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.commerces.create', 'provider.commerces.update']);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    private function storeCommercePayload(User $user, string $establishmentTypeCode, array $overrides = []): array
    {
        $establishmentType = EstablishmentType::factory()->create(['code' => $establishmentTypeCode]);
        $commerce = Commerce::factory(['owner_user_id' => $user->id])->make()->toArray();
        $commerce['establishment_type_id'] = $establishmentType->id;

        unset($commerce['operates_under_franchise']);

        return array_merge($commerce, $overrides);
    }

    public function test_create_requires_franchise_declaration_for_restaurant(): void
    {
        $user = $this->actingAsProvider();
        $payload = $this->storeCommercePayload($user, Constant::ESTABLISHMENT_TYPE_RESTAURANT);

        $response = $this->postJson('/api/v1/commerces', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['operates_under_franchise']);
    }

    public function test_create_rejects_franchise_declaration_for_retail(): void
    {
        $user = $this->actingAsProvider();
        $payload = $this->storeCommercePayload($user, Constant::ESTABLISHMENT_TYPE_RETAIL, [
            'operates_under_franchise' => false,
        ]);

        $response = $this->postJson('/api/v1/commerces', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['operates_under_franchise']);
    }

    public function test_create_without_establishment_type_id_fails(): void
    {
        $user = $this->actingAsProvider();
        $payload = Commerce::factory(['owner_user_id' => $user->id])->make()->toArray();
        unset($payload['establishment_type_id'], $payload['operates_under_franchise']);

        $response = $this->postJson('/api/v1/commerces', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['establishment_type_id']);
    }

    public function test_create_records_franchise_declaration_with_provenance(): void
    {
        $user = $this->actingAsProvider();
        $payload = $this->storeCommercePayload($user, Constant::ESTABLISHMENT_TYPE_RESTAURANT, [
            'operates_under_franchise' => true,
        ]);

        $response = $this->postJson('/api/v1/commerces', $payload);

        $response->assertCreated();
        $commerceId = $response->json('data.id');

        $this->assertDatabaseHas('commerce_franchise_declarations', [
            'commerce_id' => $commerceId,
            'operates_under_franchise' => 1,
            'declared_by_user_id' => $user->id,
        ]);

        $declaration = \App\Models\CommerceFranchiseDeclaration::where('commerce_id', $commerceId)->first();
        $this->assertNotEmpty($declaration->ip_address);
    }

    public function test_update_records_new_declaration_only_when_value_changes(): void
    {
        $user = $this->actingAsProvider();
        $payload = $this->storeCommercePayload($user, Constant::ESTABLISHMENT_TYPE_RESTAURANT, [
            'operates_under_franchise' => false,
        ]);
        $commerceId = $this->postJson('/api/v1/commerces', $payload)->json('data.id');

        $this->assertDatabaseCount('commerce_franchise_declarations', 1);

        // Reenviar el mismo valor (PUT reenvía el payload completo en cada edición)
        // no debe crear una fila nueva.
        $unchangedPayload = $payload;
        $unchangedPayload['name'] = 'Nombre editado';
        $this->putJson('/api/v1/commerces/'.$commerceId, $unchangedPayload)->assertOk();

        $this->assertDatabaseCount('commerce_franchise_declarations', 1);

        // Cambiar el valor sí debe crear una fila nueva, preservando la anterior.
        $changedPayload = $payload;
        $changedPayload['operates_under_franchise'] = true;
        $this->putJson('/api/v1/commerces/'.$commerceId, $changedPayload)->assertOk();

        $this->assertDatabaseCount('commerce_franchise_declarations', 2);
        $this->assertDatabaseHas('commerce_franchise_declarations', [
            'commerce_id' => $commerceId,
            'operates_under_franchise' => 1,
        ]);
        $this->assertDatabaseHas('commerce_franchise_declarations', [
            'commerce_id' => $commerceId,
            'operates_under_franchise' => 0,
        ]);
    }

    public function test_update_without_establishment_type_id_fails(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create([
            'owner_user_id' => $user->id,
            'establishment_type_id' => EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_RETAIL])->id,
        ]);

        $payload = $commerce->toArray();
        unset($payload['establishment_type_id']);

        $response = $this->putJson('/api/v1/commerces/'.$commerce->id, $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['establishment_type_id']);
    }

    public function test_basic_data_requires_franchise_declaration_for_bakery(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.create');
        $this->actingAs($user, 'sanctum');

        $establishmentType = EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_BAKERY]);

        $payload = [
            'commerce' => [
                'owner_user_id' => $user->id,
                'department_id' => \App\Models\Department::factory()->create()->id,
                'city_id' => \App\Models\City::factory()->create()->id,
                'neighborhood_id' => \App\Models\Neighborhood::factory()->create()->id,
                'establishment_type_id' => $establishmentType->id,
                'name' => 'Panadería Test',
                'tax_id' => '123456789',
                'tax_id_type' => 'NIT',
                'address' => 'Calle 1 #2-3',
                'electronic_invoicing_required' => false,
            ],
            'legal_representative' => [
                'name' => 'Juan',
                'last_name' => 'Pérez',
                'document' => '12345678',
                'document_type' => 'CC',
                'is_primary' => true,
            ],
        ];

        $response = $this->postJson('/api/v1/commerces/basic', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commerce.operates_under_franchise']);
    }

    public function test_basic_data_records_franchise_declaration(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.create');
        $this->actingAs($user, 'sanctum');

        $establishmentType = EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_RESTAURANT]);

        $payload = [
            'commerce' => [
                'owner_user_id' => $user->id,
                'department_id' => \App\Models\Department::factory()->create()->id,
                'city_id' => \App\Models\City::factory()->create()->id,
                'neighborhood_id' => \App\Models\Neighborhood::factory()->create()->id,
                'establishment_type_id' => $establishmentType->id,
                'operates_under_franchise' => true,
                'name' => 'Restaurante Test',
                'tax_id' => '123456789',
                'tax_id_type' => 'NIT',
                'address' => 'Calle 1 #2-3',
                'electronic_invoicing_required' => false,
            ],
            'legal_representative' => [
                'name' => 'Juan',
                'last_name' => 'Pérez',
                'document' => '12345678',
                'document_type' => 'CC',
                'is_primary' => true,
            ],
        ];

        $response = $this->postJson('/api/v1/commerces/basic', $payload);

        $response->assertCreated();
        $commerceId = $response->json('data.commerce.id');

        $this->assertDatabaseHas('commerce_franchise_declarations', [
            'commerce_id' => $commerceId,
            'operates_under_franchise' => 1,
            'declared_by_user_id' => $user->id,
        ]);
    }
}
