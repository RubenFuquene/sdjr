<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Bank;
use App\Models\City;
use App\Models\Department;
use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommerceBasicDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.commerces.create', 'sanctum');
    }

    public function test_user_can_create_commerce_with_basic_data()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.create');
        $this->actingAs($user, 'sanctum');

        $bank = Bank::factory()->create();
        $department = Department::factory()->create();
        $city = City::factory()->create(['department_id' => $department->id]);
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id]);
        $payload = [
            'commerce' => [
                'owner_user_id' => $user->id,
                'department_id' => $department->id,
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'name' => 'Comercio Test',
                'description' => 'Comercio de prueba',
                'tax_id' => '123456789',
                'tax_id_type' => 'NIT',
                'address' => 'Calle 1 #2-3',
                'phone' => '3001234567',
                'email' => 'test@comercio.com',
                'is_verified' => false,
                'is_active' => true,
            ],
            'legal_representative' => [

                'name' => 'Juan',
                'last_name' => 'Pérez',
                'document' => '12345678',
                'document_type' => 'CC',
                'email' => 'juan@comercio.com',
                'phone' => '3001234567',
                'is_primary' => true,
            ],
            'commerce_documents' => [
                [
                    'verified_by_id' => null,
                    'uploaded_by_id' => $user->id,
                    'document_type' => 'RUT',
                    'file_path' => 'docs/rut.pdf',
                    'mime_type' => 'application/pdf',
                    'verified' => false,
                    'uploaded_at' => now()->toDateString(),
                    'verified_at' => null,
                ],
            ],
            'my_account' => [
                'type' => 'bank',
                'account_type' => 'savings',
                'bank_id' => $bank->id,
                'account_number' => '1234567890',
                'owner_id' => $user->id,
                'is_primary' => true,
            ],
        ];

        $response = $this->postJson('/api/v1/commerces/basic', $payload);
        $response->assertCreated();
        $response->assertJsonPath('status', true);
        $response->assertJsonStructure(['data' => ['commerce', 'commerce_documents', 'my_account']]);
    }

    public function test_cannot_create_commerce_basic_data_without_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $bank = Bank::factory()->create();
        $department = Department::factory()->create();
        $city = City::factory()->create(['department_id' => $department->id]);
        $neighborhood = Neighborhood::factory()->create(['city_id' => $city->id]);
        $payload = [
            'commerce' => [
                'owner_user_id' => $user->id,
                'department_id' => $department->id,
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'name' => 'Comercio Test',
                'description' => 'Comercio de prueba',
                'tax_id' => '123456789',
                'tax_id_type' => 'NIT',
                'address' => 'Calle 1 #2-3',
                'phone' => '3001234567',
                'email' => 'test@comercio.com',
                'is_verified' => false,
                'is_active' => true,
            ],
            'my_account' => [
                'type' => 'bank',
                'account_type' => 'savings',
                'bank_id' => $bank->id,
                'account_number' => '1234567890',
                'owner_id' => $user->id,
                'is_primary' => true,
            ],
        ];
        $response = $this->postJson('/api/v1/commerces/basic', $payload);
        $response->assertForbidden();
    }

    public function test_validation_error_on_missing_required_fields()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.create');
        $this->actingAs($user, 'sanctum');
        $response = $this->postJson('/api/v1/commerces/basic', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'commerce.owner_user_id',
            'commerce.department_id',
            'commerce.city_id',
            'commerce.neighborhood_id',
            'commerce.name',
            'commerce.tax_id',
            'commerce.tax_id_type',
            'commerce.address',
            'my_account.type',
            'my_account.account_type',
            'my_account.bank_id',
            'my_account.account_number',
            'my_account.owner_id',
        ]);
    }
}
