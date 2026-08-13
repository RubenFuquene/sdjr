<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Enums\FiscalCode;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * SCRUM-376: order_items congela la clasificación fiscal vigente del
 * producto al momento de la venta — mismo patrón que unit_price. El futuro
 * motor de FAN (SCRUM-252) debe leer de aquí, nunca de products.
 */
class OrderFiscalSnapshotTest extends TestCase
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

    public function test_order_item_freezes_the_products_fiscal_classification(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => FiscalCode::Vat19General,
            'vat_rate' => 19.0,
            'applies_inc' => false,
            'inc_rate' => 0.0,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('order_items', [
            'order_id' => Order::latest('id')->first()->id,
            'product_id' => $product->id,
            'fiscal_code' => FiscalCode::Vat19General->value,
            'vat_rate' => 19.00,
            'applies_inc' => false,
            'inc_rate' => 0.00,
        ]);
    }

    public function test_reclassifying_the_product_does_not_alter_an_existing_order_item(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $product = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => FiscalCode::Vat19General,
            'vat_rate' => 19.0,
            'applies_inc' => false,
            'inc_rate' => 0.0,
        ]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $orderItem = Order::latest('id')->first()->items()->first();

        $product->update([
            'fiscal_code' => FiscalCode::Vat5Special,
            'vat_rate' => 5.0,
        ]);

        $orderItem->refresh();
        $this->assertSame(FiscalCode::Vat19General, $orderItem->fiscal_code);
        $this->assertSame(19.0, $orderItem->vat_rate);
        $this->assertSame(FiscalCode::Vat5Special, $product->fresh()->fiscal_code);
    }

    public function test_package_parent_line_has_no_fiscal_snapshot_while_children_carry_their_own(): void
    {
        $this->actingAsCustomer();
        $branch = CommerceBranch::factory()->create();

        $componentA = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => FiscalCode::Vat19General,
            'vat_rate' => 19.0,
        ]);
        $componentB = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'fiscal_code' => FiscalCode::Vat5Special,
            'vat_rate' => 5.0,
        ]);
        $componentA->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => false]);
        $componentB->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => false]);

        $pack = Product::factory()->create([
            'commerce_id' => $branch->commerce_id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'fiscal_code' => null,
            'vat_rate' => null,
            'applies_inc' => null,
            'inc_rate' => null,
        ]);
        $pack->packageItems()->attach($componentA->id, ['quantity' => 1]);
        $pack->packageItems()->attach($componentB->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 1],
            ],
        ]);

        $response->assertCreated();

        $lines = OrderItem::where('order_id', Order::latest('id')->first()->id)->get();

        $parentLine = $lines->firstWhere('product_id', $pack->id);
        $this->assertNotNull($parentLine);
        $this->assertNull($parentLine->fiscal_code);
        $this->assertNull($parentLine->vat_rate);

        $childLineA = $lines->firstWhere('product_id', $componentA->id);
        $this->assertNotNull($childLineA);
        $this->assertSame(FiscalCode::Vat19General, $childLineA->fiscal_code);
        $this->assertSame(19.0, $childLineA->vat_rate);

        $childLineB = $lines->firstWhere('product_id', $componentB->id);
        $this->assertNotNull($childLineB);
        $this->assertSame(FiscalCode::Vat5Special, $childLineB->fiscal_code);
        $this->assertSame(5.0, $childLineB->vat_rate);
    }
}
