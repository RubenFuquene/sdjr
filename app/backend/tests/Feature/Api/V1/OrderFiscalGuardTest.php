<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362 (4.2, CR-01): un producto con fiscal_code = otro_verificar
 * nunca puede terminar en un carrito pagado, suelto o dentro de un pack.
 */
class OrderFiscalGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('customer.orders.create', 'sanctum');
    }

    private function actingAsCustomer(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_cannot_order_a_product_pending_review(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
        ]);
        // Publicado a la fuerza vía pivote directo — cubre el caso de un
        // producto que llegó a este estado antes de existir la guarda (o
        // vía manipulación directa), no solo el camino feliz de la API.
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_order_a_package_whose_component_is_pending_review(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $component = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
        ]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => false]);

        $pack = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($component->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_can_order_a_product_with_a_real_fiscal_classification(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create(['commerce_id' => $branch->commerce_id]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated();
    }
}
