<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-375: el punto de venta revalida las mismas compuertas que el
 * catálogo aplica para mostrar un producto — is_published en la sede,
 * status activo y expires_at vigente. Antes de esta tarea, un producto
 * despublicado/inactivo/vencido era comprable si tenía stock en el pivote.
 */
class OrderCatalogGatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('customer.orders.create', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
    }

    private function actingAsCustomer(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($user);

        return $user;
    }

    public function test_cannot_order_an_unpublished_product(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create(['commerce_id' => $branch->commerce_id]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => false]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_order_an_inactive_product(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'status' => Constant::STATUS_INACTIVE,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_order_an_expired_product(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'expires_at' => now()->subDay(),
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_can_order_a_published_active_non_expired_product(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        // status activo por defecto en el factory; expires_at nulo (no vence).
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

    /**
     * D6: la guarda de catálogo no desciende a componentes de pack — el
     * compromiso de un pack es sobre el stock de sus componentes, no sobre
     * si cada uno está publicado individualmente. Un componente puede
     * venderse solo dentro de un pack, sin venderse suelto.
     */
    public function test_can_order_a_package_whose_component_is_individually_unpublished(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $component = Product::factory()->create(['commerce_id' => $branch->commerce_id]);
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
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $packResponse = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 1],
            ],
        ]);
        $packResponse->assertCreated();

        $componentResponse = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $component->id, 'quantity' => 1],
            ],
        ]);
        $componentResponse->assertStatus(422);
    }

    public function test_catalog_gate_rejection_message_is_translated(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'title' => 'gaseosa familiar',
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => false]);

        $body = [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        $esResponse = $this->withHeaders(['Accept-Language' => ''])->postJson('/api/v1/orders', $body);
        $esResponse->assertStatus(422);
        $esMessage = (string) $esResponse->json('message');
        $this->assertStringContainsString($product->title, $esMessage);

        $enResponse = $this->withHeaders(['Accept-Language' => 'en'])->postJson('/api/v1/orders', $body);
        $enResponse->assertStatus(422);
        $enMessage = (string) $enResponse->json('message');
        $this->assertStringContainsString($product->title, $enMessage);
        $this->assertNotSame($esMessage, $enMessage);
    }

    /**
     * SCRUM-375 (D5): antes de esta tarea, el rechazo por stock devolvía un
     * literal hardcodeado en inglés ('One or more products are not
     * available in the requested quantity'), sin pasar por i18n.
     */
    public function test_insufficient_stock_rejection_message_is_translated(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'title' => 'edicion limitada',
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 0, 'is_published' => true]);

        $body = [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        $esResponse = $this->withHeaders(['Accept-Language' => ''])->postJson('/api/v1/orders', $body);
        $esResponse->assertStatus(422);
        $esMessage = (string) $esResponse->json('message');
        $this->assertStringContainsString($product->title, $esMessage);
        $this->assertStringNotContainsString('One or more products', $esMessage);

        $enResponse = $this->withHeaders(['Accept-Language' => 'en'])->postJson('/api/v1/orders', $body);
        $enResponse->assertStatus(422);
        $enMessage = (string) $enResponse->json('message');
        $this->assertStringContainsString($product->title, $enMessage);
        $this->assertNotSame($esMessage, $enMessage);
    }

    /**
     * Escenario integrado: el toggle real de publicación (el mismo endpoint
     * que usa el panel de proveedor) despublica el producto, y esa
     * despublicación por sí sola — sin ningún motivo fiscal de por medio —
     * ahora sí protege el punto de venta. Es la demostración más directa
     * del hueco que cierra este ticket: antes de esta tarea, esta misma
     * secuencia terminaba en 201 (compra exitosa) porque
     * validateProductAvailability() nunca miraba is_published.
     */
    public function test_toggling_publication_off_blocks_a_subsequent_purchase(): void
    {
        $provider = User::factory()->create();
        $provider->givePermissionTo('provider.products.update');
        $commerce = Commerce::factory()->create(['owner_user_id' => $provider->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        Sanctum::actingAs($provider);
        $this->patchJson("/api/v1/products/{$product->id}/branches/{$branch->id}", [
            'is_published' => false,
        ])->assertOk();

        $this->actingAsCustomer();
        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
    }
}
