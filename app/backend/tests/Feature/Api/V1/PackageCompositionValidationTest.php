<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SCRUM-361, Tarea 4 (SCRUM-323): un componente debe tener inventario
 * asignado en cada sede a la que se asigna el pack. Publicado no se exige
 * — un aliado puede vender un producto solo dentro de packs.
 */
class PackageCompositionValidationTest extends TestCase
{
    use RefreshDatabase;

    private function ownerWithCommerce(): array
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        return [$user, $commerce];
    }

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Permission::findOrCreate('provider.products.create', 'sanctum');
        \Spatie\Permission\Models\Permission::findOrCreate('provider.products.update', 'sanctum');
    }

    public function test_store_rejects_a_component_without_stock_in_the_packs_branch(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        // El componente existe pero nunca se le asignó esta sede.
        $component = Product::factory()->create(['commerce_id' => $commerce->id, 'title' => 'Croissant']);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack sin stock',
                'product_type' => 'package',
                'original_price' => 100,
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 1],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['package_items.0.product_id']);
        $message = $response->json('errors')['package_items.0.product_id'][0];
        $this->assertStringContainsString('Croissant', $message);
        $this->assertStringContainsString($branch->name, $message);
    }

    public function test_store_accepts_a_component_with_stock_but_not_published(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        // Asignado con stock, pero NO publicado — un aliado puede vender un
        // producto solo dentro de packs.
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack valido',
                'product_type' => 'package',
                'original_price' => $component->currentSalePrice(),
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5],
            ],
        ]);

        $response->assertOk();
    }

    /**
     * Ajuste funcional 2026-08-03: un pack se ofrece en una sola sede.
     * Reemplaza a un test anterior que armaba un pack en dos sedes para
     * probar la intersección de candidatos — ese escenario ya no es
     * alcanzable, porque ahora se rechaza antes, por esta regla. El caso de
     * "componente sin stock en la sede del pack" sigue cubierto arriba.
     */
    public function test_update_rejects_adding_a_second_branch_to_an_existing_package(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branchA->id, ['quantity_available' => 5, 'is_published' => true]);
        $component->commerceBranches()->attach($branchB->id, ['quantity_available' => 5, 'is_published' => true]);

        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $package->commerceBranches()->attach($branchA->id, ['quantity_available' => 2, 'is_published' => false]);
        $package->packageItems()->attach($component->id, ['quantity' => 1]);

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branchA->id, 'quantity_available' => 2, 'is_published' => false],
                ['commerce_branch_id' => $branchB->id, 'quantity_available' => 2, 'is_published' => false],
            ],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['commerce_branches']);
        // La asignación original a la sede A permanece intacta.
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $package->id,
            'commerce_branch_id' => $branchA->id,
            'quantity_available' => 2,
        ]);
        $this->assertDatabaseMissing('product_commerce_branch', [
            'product_id' => $package->id,
            'commerce_branch_id' => $branchB->id,
        ]);
    }

    /**
     * Ticket derivado de SCRUM-361/323 (2026-08-04): el techo del pack es la
     * suma de los precios YA CON DESCUENTO de sus componentes, no de sus
     * precios de lista — de lo contrario un pack sin descuento propio podía
     * costar más que comprar las partes sueltas.
     */
    public function test_store_accepts_a_package_priced_at_the_sum_of_discounted_component_prices(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $componentA = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 15000,
            'discounted_price' => 12000,
        ]);
        $componentB = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 15000,
            'discounted_price' => 12000,
        ]);
        $componentA->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);
        $componentB->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack con descuento',
                'product_type' => 'package',
                // 12.000 + 12.000 = 24.000, no 15.000 + 15.000 = 30.000.
                'original_price' => 24000,
            ],
            'package_items' => [
                ['product_id' => $componentA->id, 'quantity' => 1],
                ['product_id' => $componentB->id, 'quantity' => 1],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'title' => 'Pack Con Descuento',
            'original_price' => 24000,
        ]);
    }

    public function test_store_rejects_a_package_priced_above_the_sum_of_discounted_component_prices(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 15000,
            'discounted_price' => 12000,
        ]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->postJson('/api/v1/products/commerce/package-items', [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack sobrevalorado',
                'product_type' => 'package',
                // El precio de lista (15.000), no el techo real (12.000).
                'original_price' => 15000,
            ],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['product.original_price']);
        $message = $response->json('errors')['product.original_price'][0];
        $this->assertStringContainsString('12000', $message);
    }

    public function test_update_resolves_branches_from_database_when_payload_omits_them(): void
    {
        [$user, $commerce] = $this->ownerWithCommerce();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);
        $package->commerceBranches()->attach($branch->id, ['quantity_available' => 2, 'is_published' => false]);
        $package->packageItems()->attach($component->id, ['quantity' => 2]);

        // Edita package_items sin reenviar commerce_branches: el valor
        // efectivo debe resolver la sede ya asignada (la misma $branch) y
        // validar el nuevo componente contra ella.
        $otherComponent = Product::factory()->create(['commerce_id' => $commerce->id]);
        // No tiene stock en $branch: debe rechazarse usando la sede heredada de BD.

        $response = $this->putJson('/api/v1/products/commerce/package-items/'.$package->id, [
            'product' => ['commerce_id' => $commerce->id],
            'package_items' => [
                ['product_id' => $otherComponent->id, 'quantity' => 1],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['package_items.0.product_id']);
        $this->assertStringContainsString($branch->name, $response->json('errors')['package_items.0.product_id'][0]);
    }
}
