<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\EstablishmentType;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362 (3.6): default_fiscal_code de una categoría debe ser coherente
 * con su propio establishment_type_id — una categoría de Retail no puede
 * sugerir Impoconsumo.
 */
class ProductCategoryFiscalCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.product_categories.create', 'sanctum');
        Permission::findOrCreate('provider.product_categories.update', 'sanctum');
    }

    private function actingAsProvider(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.product_categories.create', 'provider.product_categories.update']);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_retail_category_cannot_default_to_inc(): void
    {
        $this->actingAsProvider();
        $type = EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_RETAIL]);

        $response = $this->postJson('/api/v1/product-categories', [
            'establishment_type_id' => $type->id,
            'name' => 'Snacks',
            'default_fiscal_code' => 'inc_8_preparado',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['default_fiscal_code']);
    }

    public function test_restaurant_category_can_default_to_inc(): void
    {
        $this->actingAsProvider();
        $type = EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_RESTAURANT]);

        $response = $this->postJson('/api/v1/product-categories', [
            'establishment_type_id' => $type->id,
            'name' => 'Platos fuertes',
            'default_fiscal_code' => 'inc_8_preparado',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('product_categories', [
            'name' => 'Platos fuertes',
            'default_fiscal_code' => 'inc_8_preparado',
        ]);
    }

    public function test_category_without_establishment_type_accepts_any_default(): void
    {
        $this->actingAsProvider();

        $response = $this->postJson('/api/v1/product-categories', [
            'name' => 'Genérica',
            'default_fiscal_code' => 'iva_19_general',
        ]);

        $response->assertCreated();
    }

    public function test_update_validates_default_against_existing_establishment_type(): void
    {
        $this->actingAsProvider();
        $type = EstablishmentType::factory()->create(['code' => Constant::ESTABLISHMENT_TYPE_RETAIL]);
        $category = ProductCategory::factory()->create(['establishment_type_id' => $type->id]);

        $response = $this->putJson('/api/v1/product-categories/'.$category->id, [
            'default_fiscal_code' => 'inc_8_preparado',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['default_fiscal_code']);
    }
}
