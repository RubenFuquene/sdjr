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
 * SCRUM-362 (4.4/4.5, D9): reclasificar a otro_verificar despublica en
 * cascada sedes y packs — pero solo con confirmación explícita si hay
 * impacto real.
 */
class ProductFiscalReclassificationCascadeTest extends TestCase
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

    public function test_reclassification_with_impact_requires_confirmation(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        $product->commerceBranches()->attach($branchA->id, ['quantity_available' => 5, 'is_published' => true]);
        $product->commerceBranches()->attach($branchB->id, ['quantity_available' => 5, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($product->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branchA->id, ['quantity_available' => 3, 'is_published' => true]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar'],
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('errors.affected_branches.0.commerce_branch_id', $branchA->id);
        $response->assertJsonPath('errors.affected_branches.1.commerce_branch_id', $branchB->id);
        $response->assertJsonPath('errors.affected_packages.0.package_id', $pack->id);

        // Nada se aplicó — ni el fiscal_code, ni la publicación.
        $this->assertDatabaseHas('products', ['id' => $product->id, 'fiscal_code' => 'iva_19_general']);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchA->id,
            'is_published' => 1,
        ]);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $pack->id,
            'commerce_branch_id' => $branchA->id,
            'is_published' => 1,
        ]);
    }

    public function test_confirmed_reclassification_cascades_unpublish(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branchA = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $branchB = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        $product->commerceBranches()->attach($branchA->id, ['quantity_available' => 5, 'is_published' => true]);
        $product->commerceBranches()->attach($branchB->id, ['quantity_available' => 5, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($product->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branchA->id, ['quantity_available' => 3, 'is_published' => true]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar'],
            'confirm_fiscal_reclassification' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'fiscal_code' => 'otro_verificar']);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchA->id,
            'is_published' => 0,
        ]);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branchB->id,
            'is_published' => 0,
        ]);
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $pack->id,
            'commerce_branch_id' => $branchA->id,
            'is_published' => 0,
        ]);
    }

    /**
     * SCRUM-362: el formulario real de edición reenvía siempre el estado
     * completo de commerce_branches, incluidas las sedes que ya estaban
     * publicadas y que el aliado no tocó — detectado por verificación manual
     * en navegador, no por los tests con payload mínimo de arriba. Sin
     * distinguir "ya estaba publicado" de "se está publicando ahora", el
     * guard de 4.1 bloqueaba con 422 antes de que la cascada de 4.4/4.5
     * tuviera oportunidad de ofrecer el 409 — dejando esa ruta inalcanzable
     * desde el formulario real.
     */
    public function test_reclassification_requires_confirmation_even_when_branches_are_resent_unchanged(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar'],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5, 'is_published' => true],
            ],
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('errors.affected_branches.0.commerce_branch_id', $branch->id);

        $confirmed = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar'],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5, 'is_published' => true],
            ],
            'confirm_fiscal_reclassification' => true,
        ]);

        $confirmed->assertOk();
        $this->assertDatabaseHas('product_commerce_branch', [
            'product_id' => $product->id,
            'commerce_branch_id' => $branch->id,
            'is_published' => 0,
        ]);
    }

    public function test_genuinely_new_publish_attempt_is_still_blocked_while_pending(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id],
            'commerce_branches' => [
                ['commerce_branch_id' => $branch->id, 'quantity_available' => 5, 'is_published' => true],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commerce_branches.0.is_published']);
    }

    public function test_reclassification_without_impact_needs_no_confirmation(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);

        // Sin sedes asignadas y sin pertenecer a ningún pack.
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar'],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'fiscal_code' => 'otro_verificar']);
    }

    public function test_reclassification_to_the_same_pending_value_is_a_noop(): void
    {
        $user = $this->actingAsProvider();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
            'original_price' => 10,
            'discounted_price' => 10,
        ]);
        // is_published queda en false porque otro guard (4.1) ya lo impide,
        // pero esto confirma que reenviar el mismo valor no dispara la
        // detección de transición ni exige confirmación.
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => false]);

        $response = $this->putJson('/api/v1/products/'.$product->id, [
            'product' => ['commerce_id' => $commerce->id, 'fiscal_code' => 'otro_verificar', 'title' => 'Renombrado'],
        ]);

        $response->assertOk();
    }
}
