<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362: PATCH /products/{id}/branches/{branchId} (el toggle de publicar
 * de la tarjeta) no revisaba si un pack tenía algún componente pendiente de
 * revisión fiscal — el guard existente solo miraba $product->fiscal_code,
 * que para un pack siempre es null. Bug real reportado en producción (hoy,
 * mismo commit que lo introdujo, 098597f3): un pack con un componente
 * otro_verificar se podía publicar igual desde este endpoint, a diferencia
 * de Store/Update, que sí revisaban package_items correctamente.
 */
class ProductBranchPublicationFiscalGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('provider.products.update', 'sanctum');
    }

    private function actingAsProvider(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.update');
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_cannot_publish_a_package_with_a_pending_review_component(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $pendingComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'title' => 'Chocolate',
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => null,
        ]);
        $pendingComponent->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($pendingComponent->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->patchJson("/api/v1/products/{$pack->id}/branches/{$branch->id}", [
                'is_published' => true,
            ]);

        $response->assertStatus(422);
        $this->assertSame(
            "No se puede publicar el pack: el producto 'Chocolate' tiene su clasificación fiscal pendiente de revisión.",
            $response->json('errors.is_published.0')
        );
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $pack->id,
            'commerce_branch_id' => $branch->id,
            'is_published' => false,
        ]);
    }

    public function test_pending_review_component_check_runs_before_capacity_check(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        // A propósito, insuficiente stock TAMBIÉN — el mensaje debe ser el
        // fiscal (precedencia ya decidida hoy: es el bloqueo más duro).
        $pendingComponent = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'title' => 'Chocolate',
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => null,
        ]);
        $pendingComponent->commerceBranches()->attach($branch->id, ['quantity_available' => 1, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($pendingComponent->id, ['quantity' => 5]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->patchJson("/api/v1/products/{$pack->id}/branches/{$branch->id}", [
                'is_published' => true,
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString(
            'pendiente de revisión',
            $response->json('errors.is_published.0')
        );
    }

    public function test_can_publish_a_package_when_all_components_are_classified(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $component = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'iva_19_general',
            'vat_rate' => 19,
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
        $pack->packageItems()->attach($component->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->patchJson("/api/v1/products/{$pack->id}/branches/{$branch->id}", [
            'is_published' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $pack->id,
            'commerce_branch_id' => $branch->id,
            'is_published' => true,
        ]);
    }

    public function test_single_product_pending_review_message_in_english_when_requested(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => null,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => false]);

        $response = $this->withHeaders(['Accept-Language' => 'en'])
            ->patchJson("/api/v1/products/{$product->id}/branches/{$branch->id}", [
                'is_published' => true,
            ]);

        $response->assertStatus(422);
        $this->assertSame(
            'Cannot publish a product with a pending fiscal classification.',
            $response->json('errors.is_published.0')
        );
    }

    /**
     * SCRUM-227: repro exacto reportado por el usuario — este mensaje ya
     * existía desde hoy (098597f3) pero nunca había pasado por i18n.
     */
    public function test_single_product_pending_review_message_in_spanish_by_default(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => null,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => false]);

        $response = $this->withHeaders(['Accept-Language' => ''])
            ->patchJson("/api/v1/products/{$product->id}/branches/{$branch->id}", [
                'is_published' => true,
            ]);

        $response->assertStatus(422);
        $this->assertSame(
            'No se puede publicar un producto con clasificación fiscal pendiente de revisión.',
            $response->json('errors.is_published.0')
        );
    }
}
