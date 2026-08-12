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
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-227: los 5 mensajes de validación de composición de pack
 * (StoreProductRequest/UpdateProductRequest::validatePackageComposition())
 * estaban hardcodeados en inglés, sin pasar por i18n — código nuestro,
 * introducido entre 2026-08-04 y 2026-08-10 (SCRUM-361/323/362).
 */
class ProductPackageCompositionLocaleTest extends TestCase
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

    private function packagePayload(Commerce $commerce, ProductCategory $category, CommerceBranch $branch, array $packageItems, float $originalPrice, int $branchQuantity = 1): array
    {
        return [
            'product' => [
                'commerce_id' => $commerce->id,
                'product_category_id' => $category->id,
                'title' => 'Pack QA composición',
                'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
                'original_price' => $originalPrice,
                'quantity_total' => $branchQuantity,
                'quantity_available' => $branchQuantity,
            ],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => $branchQuantity],
            ],
            'package_items' => $packageItems,
        ];
    }

    /**
     * Las claves de $validator->errors()->add('a.b.c', ...) son literales
     * con puntos, no rutas anidadas — assertJsonPath las interpreta mal
     * (navega "a" → "b" → "c" en vez de buscar la clave plana "a.b.c").
     * Se lee directo del array de errores decodificado.
     */
    private function assertFirstError(TestResponse $response, string $key, string $expected): void
    {
        $response->assertStatus(422);
        $this->assertSame($expected, $response->json('errors')[$key][0] ?? null);
    }

    // --- pending_review ---

    public function test_pending_review_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $pending = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'title' => 'Chocolate',
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => null,
        ]);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $pending->id, 'quantity' => 1],
        ], 10);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'package_items.0.product_id',
            "El producto 'Chocolate' no se puede agregar a un pack mientras su clasificación fiscal esté pendiente de revisión."
        );
    }

    public function test_pending_review_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $pending = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'title' => 'Chocolate',
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => null,
        ]);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $pending->id, 'quantity' => 1],
        ], 10);

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'package_items.0.product_id',
            "The product 'Chocolate' cannot be added to a package while its fiscal classification is pending review."
        );
    }

    // --- not_single_type ---

    public function test_not_single_type_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $anotherPackage = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'title' => 'Pack anidado',
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $anotherPackage->id, 'quantity' => 1],
        ], 10);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'package_items.0.product_id',
            "El producto '{$anotherPackage->title}' debe ser de tipo 'individual' para incluirse en un pack."
        );
    }

    public function test_not_single_type_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $anotherPackage = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'title' => 'Pack anidado',
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
        ]);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $anotherPackage->id, 'quantity' => 1],
        ], 10);

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'package_items.0.product_id',
            "The product '{$anotherPackage->title}' must be of type 'single' to be included in a package."
        );
    }

    // --- missing_stock ---

    public function test_missing_stock_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id, 'name' => 'Sede QA']);
        // Componente sin stock asignado en $branch a propósito.
        $component = Product::factory()->create(['commerce_id' => $commerce->id, 'title' => 'Sin stock']);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $component->id, 'quantity' => 1],
        ], 10);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'package_items.0.product_id',
            "El producto '{$component->title}' no tiene stock asignado en la sede '{$branch->name}'."
        );
    }

    public function test_missing_stock_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id, 'name' => 'Sede QA']);
        $component = Product::factory()->create(['commerce_id' => $commerce->id, 'title' => 'Sin stock']);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $component->id, 'quantity' => 1],
        ], 10);

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'package_items.0.product_id',
            "The product '{$component->title}' has no stock assigned in branch '{$branch->name}'."
        );
    }

    // --- max_packs_exceeded ---

    public function test_max_packs_exceeded_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id, 'name' => 'Sede QA']);
        $component = Product::factory()->create(['commerce_id' => $commerce->id, 'title' => 'Componente']);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 4, 'is_published' => true]);

        // quantityPerPack=2 sobre 4 unidades => máximo 2 packs; se piden 5.
        $payload = $this->packagePayload(
            $commerce,
            $category,
            $branch,
            [['product_id' => $component->id, 'quantity' => 2]],
            $component->currentSalePrice() * 2,
            branchQuantity: 5
        );

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'commerce_branches.0.quantity_available',
            "La cantidad solicitada (5) supera el máximo de packs disponibles en la sede '{$branch->name}' según el stock actual (máx: 2)."
        );
    }

    public function test_max_packs_exceeded_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id, 'name' => 'Sede QA']);
        $component = Product::factory()->create(['commerce_id' => $commerce->id, 'title' => 'Componente']);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 4, 'is_published' => true]);

        $payload = $this->packagePayload(
            $commerce,
            $category,
            $branch,
            [['product_id' => $component->id, 'quantity' => 2]],
            $component->currentSalePrice() * 2,
            branchQuantity: 5
        );

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'commerce_branches.0.quantity_available',
            "The requested quantity_available (5) exceeds the maximum packs available in branch '{$branch->name}' given current stock (max: 2)."
        );
    }

    // --- price_ceiling ---

    public function test_price_ceiling_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $expected = round($component->currentSalePrice(), 2);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $component->id, 'quantity' => 1],
        ], $expected + 999);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'product.original_price',
            "El precio del pack debe ser igual a la suma del precio actual de sus componentes (esperado: {$expected})."
        );
    }

    public function test_price_ceiling_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $category = ProductCategory::factory()->create();
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $expected = round($component->currentSalePrice(), 2);

        $payload = $this->packagePayload($commerce, $category, $branch, [
            ['product_id' => $component->id, 'quantity' => 1],
        ], $expected + 999);

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->postJson('/api/v1/products/commerce/package-items', $payload);

        $this->assertFirstError(
            $response,
            'product.original_price',
            "The package price must equal the sum of its components' current prices (expected: {$expected})."
        );
    }

    // --- UpdateProductRequest: solo los puntos donde su lógica difiere de Store ---

    /**
     * SCRUM-361: a diferencia de Store, Update valida contra las sedes ya
     * asignadas al pack en BD cuando commerce_branches no viene en el
     * payload — código distinto, mismo mensaje ya unificado con Store.
     */
    public function test_update_max_packs_exceeded_message_using_branches_inherited_from_db(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id, 'name' => 'Sede QA Update']);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 4, 'is_published' => true]);

        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'original_price' => $component->currentSalePrice() * 2,
        ]);
        // Compromiso ya guardado (5) por encima de lo que el componente soporta.
        $package->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);
        $package->packageItems()->attach($component->id, ['quantity' => 2]);

        // No se reenvía commerce_branches: Update debe heredar la sede de BD.
        $payload = [
            'product' => ['commerce_id' => $commerce->id],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 2],
            ],
        ];

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);

        $this->assertFirstError(
            $response,
            'package_items',
            "La cantidad solicitada (5) supera el máximo de packs disponibles en la sede '{$branch->name}' según el stock actual (máx: 2)."
        );
    }

    /**
     * SCRUM-361: Update calcula el precio efectivo heredando
     * product.original_price de BD cuando el campo no se reenvía —
     * código distinto de Store, mismo mensaje ya unificado.
     */
    public function test_update_price_ceiling_message_using_price_inherited_from_db(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $component = Product::factory()->create(['commerce_id' => $commerce->id]);
        $component->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $package = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            // Precio guardado deliberadamente desalineado con el componente.
            'original_price' => 999999,
        ]);
        $package->commerceBranches()->attach($branch->id, ['quantity_available' => 1, 'is_published' => false]);
        $package->packageItems()->attach($component->id, ['quantity' => 1]);

        $expected = round($component->currentSalePrice(), 2);

        // No se reenvía product.original_price: Update debe heredar el de BD.
        $payload = [
            'product' => ['commerce_id' => $commerce->id],
            'package_items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
        ];

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->putJson('/api/v1/products/commerce/package-items/'.$package->id, $payload);

        $this->assertFirstError(
            $response,
            'product.original_price',
            "El precio del pack debe ser igual a la suma del precio actual de sus componentes (esperado: {$expected})."
        );
    }
}
