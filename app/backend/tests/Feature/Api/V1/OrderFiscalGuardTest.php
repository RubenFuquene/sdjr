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
 *
 * SCRUM-376 extiende esta guarda: fiscal_code = NULL (producto anterior a
 * la migración de SCRUM-362, nunca clasificado) tampoco puede comprarse —
 * antes de esta tarea pasaba silenciosamente. El rechazo, además, ahora
 * lanza ProductFiscalClassificationUnavailableException con un mensaje
 * traducido y neutro de cara al comprador (D5 del plan), en vez del
 * mensaje genérico hardcodeado en inglés que compartía con el rechazo por
 * falta de stock.
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

    /**
     * SCRUM-376: el hueco real que cierra esta tarea. fiscal_code = NULL
     * (producto anterior a la migración de SCRUM-362) pasaba como comprable
     * porque la guarda original solo comparaba contra otro_verificar.
     */
    public function test_cannot_order_a_product_that_was_never_classified(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
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

    public function test_cannot_order_a_package_whose_component_was_never_classified(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $component = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
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

    /**
     * SCRUM-376 (D5): el mensaje nombra el producto pero no expone el
     * motivo fiscal — lo lee el comprador, no el aliado. Cubre además el
     * locale es/en (gotcha ya conocido: postJson inyecta
     * Accept-Language: en por defecto; hay que limpiarlo para probar es).
     */
    public function test_rejection_message_is_translated_and_neutral_toward_the_buyer(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'title' => 'chocolate artesanal',
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

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
        $this->assertStringNotContainsString('fiscal', mb_strtolower($esMessage));

        $enResponse = $this->withHeaders(['Accept-Language' => 'en'])->postJson('/api/v1/orders', $body);
        $enResponse->assertStatus(422);
        $enMessage = (string) $enResponse->json('message');
        $this->assertStringContainsString($product->title, $enMessage);
        $this->assertStringNotContainsString('fiscal', mb_strtolower($enMessage));
        $this->assertNotSame($esMessage, $enMessage);
    }
}
