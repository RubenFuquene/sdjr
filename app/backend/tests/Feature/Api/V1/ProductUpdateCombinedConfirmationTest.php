<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362/361 (unificación — ver plan unificacionConfirmacion409Fiscal361):
 * un solo PUT puede disparar los dos motivos de confirmación a la vez —
 * reclasificar a otro_verificar (SCRUM-362, D9) y bajar el stock de un
 * componente sobre-comprometiendo un pack (SCRUM-361, Tarea 3). Antes de
 * esta unificación, el motivo fiscal se detectaba primero y el de stock
 * nunca llegaba a evaluarse en el mismo request.
 */
class ProductUpdateCombinedConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.create', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
    }

    private function actingAsProvider(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    private function pivotFor(int $productId, int $branchId): ProductCommerceBranch
    {
        return ProductCommerceBranch::query()
            ->where('product_id', $productId)
            ->where('commerce_branch_id', $branchId)
            ->firstOrFail();
    }

    public function test_fiscal_and_stock_impact_in_the_same_submit_return_a_single_409_with_both_reasons(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $component = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($component->id, ['quantity' => 2]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar'],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 4, 'is_published' => true],
            ],
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('errors.fiscal.affected_branches.0.commerce_branch_id', $branch->id);
        $response->assertJsonPath('errors.fiscal.affected_packages.0.package_id', $pack->id);
        $response->assertJsonPath('errors.stock.affected_packages.0.package_id', $pack->id);
        $response->assertJsonPath('errors.stock.affected_packages.0.current_quantity', 5);
        $response->assertJsonPath('errors.stock.affected_packages.0.adjusted_quantity', 2);

        // Nada se aplicó — rollback completo, ambos motivos incluidos.
        $this->assertDatabaseHas('products', ['id' => $component->id, 'fiscal_code' => 'iva_19_general']);
        $this->assertSame(10, (int) $this->pivotFor($component->id, $branch->id)->quantity_available);
        $this->assertTrue((bool) $this->pivotFor($component->id, $branch->id)->is_published);
        $this->assertSame(5, (int) $this->pivotFor($pack->id, $branch->id)->quantity_available);
        $this->assertTrue((bool) $this->pivotFor($pack->id, $branch->id)->is_published);
    }

    public function test_confirming_once_applies_both_cascades_atomically(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $component = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($component->id, ['quantity' => 2]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->putJson('/api/v1/products/'.$component->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar'],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 4, 'is_published' => true],
            ],
            'confirm_changes' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $component->id, 'fiscal_code' => 'otro_verificar']);

        // Cascada fiscal: sede del componente despublicada.
        $componentPivot = $this->pivotFor($component->id, $branch->id);
        $this->assertSame(4, (int) $componentPivot->quantity_available);
        $this->assertFalse((bool) $componentPivot->is_published);

        // Pack: cascada fiscal (despublicado, porque contiene un componente
        // pendiente de revisión) Y cascada de stock (compromiso recortado a
        // lo que el nuevo stock soporta: floor(4/2) = 2) — ambas sobre el
        // mismo pivot, sin pisarse entre sí.
        $packPivot = $this->pivotFor($pack->id, $branch->id);
        $this->assertSame(2, (int) $packPivot->quantity_available);
        $this->assertFalse((bool) $packPivot->is_published);
    }
}
