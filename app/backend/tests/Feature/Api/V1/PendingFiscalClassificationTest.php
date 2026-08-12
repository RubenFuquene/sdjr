<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Commerce;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-362 (5.1, CA-09): reporte interno de productos sin clasificar.
 */
class PendingFiscalClassificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('admin.products.fiscal_review.index', 'sanctum');
        Permission::findOrCreate('provider.products.index', 'sanctum');
    }

    public function test_admin_can_list_pending_products_with_their_commerce(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.products.fiscal_review.index');
        $this->actingAs($admin, 'sanctum');

        $commerce = Commerce::factory()->create();
        $pending = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'fiscal_code' => 'otro_verificar',
            'vat_rate' => 0,
        ]);
        Product::factory()->create(['commerce_id' => $commerce->id]); // clasificado, no debe salir

        $response = $this->getJson('/api/v1/products/pending-fiscal-classification');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $pending->id);
        $response->assertJsonPath('data.0.commerce_name', $commerce->name);
    }

    public function test_filters_by_commerce_id(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.products.fiscal_review.index');
        $this->actingAs($admin, 'sanctum');

        $commerceA = Commerce::factory()->create();
        $commerceB = Commerce::factory()->create();
        Product::factory()->create(['commerce_id' => $commerceA->id, 'fiscal_code' => 'otro_verificar', 'vat_rate' => 0]);
        $pendingB = Product::factory()->create(['commerce_id' => $commerceB->id, 'fiscal_code' => 'otro_verificar', 'vat_rate' => 0]);

        $response = $this->getJson('/api/v1/products/pending-fiscal-classification?commerce_id='.$commerceB->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $pendingB->id);
    }

    public function test_provider_cannot_access_the_admin_endpoint(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.products.index');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/products/pending-fiscal-classification');

        $response->assertForbidden();
    }
}
