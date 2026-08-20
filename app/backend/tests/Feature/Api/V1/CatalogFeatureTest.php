<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_product_is_returned_with_category_name_and_commerce_name()
    {
        $commerce = Commerce::factory()->create(['name' => 'Panaderia El Trigal']);
        $category = ProductCategory::factory()->create(['name' => 'Panaderia']);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_category_id' => $category->id,
            'status' => Constant::STATUS_ACTIVE,
            'description' => 'Bolsa sorpresa con pan del dia',
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->id}");

        $response->assertOk();
        // Commerce::sanitizeText normaliza el nombre a "lower + ucfirst" (comportamiento existente del modelo).
        $response->assertJsonFragment([
            'id' => $product->id,
            'category' => 'Panaderia',
            'commerce_name' => 'Panaderia el trigal',
            'description' => 'Bolsa sorpresa con pan del dia',
        ]);
    }

    /**
     * SCRUM-277 Fase 1: el detalle público debe exponer el stock real por
     * sede — sin esto, el cliente ve 0 disponibles y el carrito no lo deja
     * comprar más de 1 unidad sin importar el stock real (bug detectado
     * después de dar la Fase 1 por completa, antes de cerrarla de verdad).
     */
    public function test_active_product_exposes_real_stock_per_published_branch(): void
    {
        $commerce = Commerce::factory()->create();
        $publishedBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $unpublishedBranch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'status' => Constant::STATUS_ACTIVE,
        ]);
        $product->commerceBranches()->attach($publishedBranch->id, [
            'quantity_available' => 27,
            'is_published' => true,
        ]);
        $product->commerceBranches()->attach($unpublishedBranch->id, [
            'quantity_available' => 99,
            'is_published' => false,
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->id}");

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data['commerce_branches'], 'Solo la sede publicada debe exponerse al cliente.');
        $this->assertSame($publishedBranch->id, $data['commerce_branches'][0]['id']);
        $this->assertSame(27, $data['commerce_branches'][0]['quantity_available']);
    }

    public function test_inactive_product_is_not_visible_by_id()
    {
        $product = Product::factory()->create(['status' => Constant::STATUS_INACTIVE]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->id}");

        $response->assertNotFound();
    }

    /**
     * SCRUM-375: el detalle público no filtraba expires_at — un producto
     * vencido seguía siendo activo y visible por id, aunque nearbyProducts()
     * ya lo excluyera del descubrimiento (asimetría detectada al planificar
     * las compuertas de catálogo en la compra).
     */
    public function test_expired_product_is_not_visible_by_id()
    {
        $product = Product::factory()->create([
            'status' => Constant::STATUS_ACTIVE,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->id}");

        $response->assertNotFound();
    }

    /**
     * expires_at es nullable y "sin fecha" significa "no vence" — un filtro
     * que solo comparara `> now()` descartaría también a estos productos,
     * que son la mayoría del catálogo.
     */
    public function test_product_without_expiration_date_is_visible_by_id()
    {
        $product = Product::factory()->create([
            'status' => Constant::STATUS_ACTIVE,
            'expires_at' => null,
        ]);

        $this->getJson("/api/v1/catalog/products/{$product->id}")->assertOk();
    }

    public function test_product_with_future_expiration_date_is_visible_by_id()
    {
        $product = Product::factory()->create([
            'status' => Constant::STATUS_ACTIVE,
            'expires_at' => now()->addYear(),
        ]);

        $this->getJson("/api/v1/catalog/products/{$product->id}")->assertOk();
    }

    public function test_nonexistent_product_returns_404()
    {
        $response = $this->getJson('/api/v1/catalog/products/999999');

        $response->assertNotFound();
    }

    public function test_active_branch_is_returned_with_commerce_name()
    {
        $commerce = Commerce::factory()->create(['name' => 'Cafe Amor Perfecto']);
        $branch = CommerceBranch::factory()->create([
            'commerce_id' => $commerce->id,
            'status' => Constant::STATUS_ACTIVE,
        ]);

        $response = $this->getJson("/api/v1/catalog/commerce-branches/{$branch->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $branch->id,
            'commerce_name' => 'Cafe amor perfecto',
        ]);
    }

    public function test_inactive_branch_is_not_visible_by_id()
    {
        $branch = CommerceBranch::factory()->create(['status' => Constant::STATUS_INACTIVE]);

        $response = $this->getJson("/api/v1/catalog/commerce-branches/{$branch->id}");

        $response->assertNotFound();
    }

    public function test_nonexistent_branch_returns_404()
    {
        $response = $this->getJson('/api/v1/catalog/commerce-branches/999999');

        $response->assertNotFound();
    }

    public function test_catalog_endpoints_do_not_require_authentication()
    {
        $product = Product::factory()->create(['status' => Constant::STATUS_ACTIVE]);
        $branch = CommerceBranch::factory()->create(['status' => Constant::STATUS_ACTIVE]);

        $this->getJson("/api/v1/catalog/products/{$product->id}")->assertOk();
        $this->getJson("/api/v1/catalog/commerce-branches/{$branch->id}")->assertOk();
    }
}
