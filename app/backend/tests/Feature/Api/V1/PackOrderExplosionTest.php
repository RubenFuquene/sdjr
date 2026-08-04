<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * SCRUM-366/367, Tarea 4-5: comprar un pack explota order_items en líneas
 * hijas de componente con el prorrateo P3 — sin afectar el total, sin
 * duplicar el descuento de stock, y sin comprometer la integridad
 * histórica si la composición del pack cambia después.
 */
class PackOrderExplosionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('user', 'sanctum');
        Permission::findOrCreate('provider.products.create', 'sanctum');
        Permission::findOrCreate('provider.products.update', 'sanctum');
        Permission::findOrCreate('customer.orders.create', 'sanctum');
        Permission::findOrCreate('customer.orders.pay', 'sanctum');
    }

    private function pivotFor(int $productId, int $branchId): ProductCommerceBranch
    {
        return ProductCommerceBranch::query()
            ->where('product_id', $productId)
            ->where('commerce_branch_id', $branchId)
            ->firstOrFail();
    }

    /**
     * @return array{owner: User, commerce: Commerce, branch: CommerceBranch, componentA: Product, componentB: Product, pack: Product}
     */
    private function buildPackWithTwoComponents(): array
    {
        $owner = User::factory()->create();
        $owner->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $owner->id]);
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
        $componentA->commerceBranches()->attach($branch->id, ['quantity_available' => 20, 'is_published' => true]);
        $componentB->commerceBranches()->attach($branch->id, ['quantity_available' => 20, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            // Techo = 12.000 + 12.000. Descuento propio del pack: 24.000 -> 18.000.
            'original_price' => 24000,
            'discounted_price' => 18000,
        ]);
        $pack->packageItems()->attach($componentA->id, ['quantity' => 1]);
        $pack->packageItems()->attach($componentB->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        return compact('owner', 'commerce', 'branch', 'componentA', 'componentB', 'pack');
    }

    public function test_buying_a_package_explodes_order_items_with_prorated_component_lines_and_discounts_stock_once(): void
    {
        ['branch' => $branch, 'componentA' => $componentA, 'componentB' => $componentB, 'pack' => $pack] =
            $this->buildPackWithTwoComponents();

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo(['customer.orders.create', 'customer.orders.pay']);
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 2],
            ],
        ]);
        $response->assertCreated();

        $order = Order::latest('id')->first();

        // El total sigue siendo lo cobrado por el pack, no lo prorateado.
        $this->assertSame(36000.0, (float) $order->total_price);

        // items() filtrada: solo la línea padre. allItems(): las 3.
        $this->assertCount(1, $order->items);
        $this->assertCount(3, $order->allItems);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $pack->id,
            'parent_package_id' => null,
            'quantity' => 2,
            'unit_price' => 18000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $componentA->id,
            'parent_package_id' => $pack->id,
            'quantity' => 2,
            'unit_price' => 9000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $componentB->id,
            'parent_package_id' => $pack->id,
            'quantity' => 2,
            'unit_price' => 9000,
        ]);

        // Invariante contable: la suma de las hijas cuadra exacto con lo
        // cobrado por la línea padre (2 packs * 18.000 = 36.000).
        $childrenTotal = $order->allItems()
            ->whereNotNull('parent_package_id')
            ->get()
            ->sum(fn ($item) => $item->quantity * $item->unit_price);
        $this->assertSame(36000.0, $childrenTotal);

        // Pagar la orden: el stock de cada componente baja UNA sola vez
        // (2 packs * 1 unidad). Si dismissProductConfirmedStock recorriera
        // también las líneas hijas (bug que esto previene), bajaría el
        // doble: 4 en vez de 2.
        $this->postJson("/api/v1/orders/{$order->id}/transactions", [])->assertCreated();

        $this->assertSame(18, (int) $this->pivotFor($componentA->id, $branch->id)->quantity_available);
        $this->assertSame(18, (int) $this->pivotFor($componentB->id, $branch->id)->quantity_available);
        // Compromiso del pack: 5 - 2 vendidos.
        $this->assertSame(3, (int) $this->pivotFor($pack->id, $branch->id)->quantity_available);
    }

    public function test_customer_can_buy_a_package_and_one_of_its_own_components_in_the_same_order(): void
    {
        ['branch' => $branch, 'componentA' => $componentA, 'pack' => $pack] = $this->buildPackWithTwoComponents();

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($customer);

        // Antes de este ticket, esto violaba unique(order_id, product_id) en
        // cuanto la explosión del pack generara una segunda fila con el
        // mismo product_id que el componente suelto.
        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 1],
                ['product_id' => $componentA->id, 'quantity' => 2],
            ],
        ]);
        $response->assertCreated();

        $order = Order::latest('id')->first();

        // Dos líneas padre (el pack y el componente suelto) + dos hijas del
        // pack (tiene dos componentes: A y B).
        $this->assertCount(2, $order->items);
        $this->assertCount(4, $order->allItems);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $componentA->id,
            'parent_package_id' => null,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $componentA->id,
            'parent_package_id' => $pack->id,
            'quantity' => 1,
        ]);
    }

    public function test_store_rejects_the_same_product_id_repeated_directly_in_the_items_payload(): void
    {
        $owner = User::factory()->create();
        $owner->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $owner->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);
        $product = Product::factory()->create(['commerce_id' => $commerce->id]);
        $product->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($customer);

        // La regla `distinct` reemplaza la garantía que daba el índice único
        // retirado de la BD para líneas pedidas directamente por el cliente.
        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertUnprocessable();
        $errorKeys = array_keys($response->json('errors'));
        $this->assertNotEmpty(array_filter($errorKeys, fn ($key) => str_starts_with($key, 'items.')));
    }

    /**
     * Caso encontrado en revisión manual: cuando la línea que absorbe el
     * residuo tiene cantidad > 1 y el residuo no es divisible entre esa
     * cantidad (1 centavo entre 2 unidades), promediar el ajuste sobrepasa
     * el objetivo al redondear (round(0.005, 2) = 0.01, no 0.005) y
     * multiplicar de vuelta por la cantidad. La corrección debe caer sobre
     * una unidad completa, partiendo la línea en dos si hace falta.
     */
    public function test_prorated_component_lines_are_exact_when_the_adjusted_line_has_a_quantity_that_does_not_evenly_divide_the_remainder(): void
    {
        $owner = User::factory()->create();
        $owner->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $owner->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        $componentA = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 50000,
            'discounted_price' => 45000,
        ]);
        $componentB = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 65000,
            'discounted_price' => 50000,
        ]);
        $componentA->commerceBranches()->attach($branch->id, ['quantity_available' => 20, 'is_published' => true]);
        $componentB->commerceBranches()->attach($branch->id, ['quantity_available' => 20, 'is_published' => true]);

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            // Techo = 45.000*2 + 50.000*1 = 140.000.
            'original_price' => 140000,
            'discounted_price' => 130000,
        ]);
        $pack->packageItems()->attach($componentA->id, ['quantity' => 2]);
        $pack->packageItems()->attach($componentB->id, ['quantity' => 1]);
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 1],
            ],
        ]);
        $response->assertCreated();

        $order = Order::latest('id')->first();
        $children = $order->allItems()->whereNotNull('parent_package_id')->get();

        // Sin la corrección por unidad, esto daba 130000.01 (redondeo hacia
        // arriba al dividir 1 centavo entre las 2 unidades de componentA).
        $this->assertSame(130000.0, $children->sum(fn ($item) => $item->quantity * $item->unit_price));
        // La cantidad total de componentA sigue siendo 2, así se reparta en
        // una o dos filas para aplicar el ajuste.
        $this->assertSame(2, (int) $children->where('product_id', $componentA->id)->sum('quantity'));
    }

    public function test_prorated_component_lines_sum_exactly_to_the_package_total_with_a_rounding_remainder(): void
    {
        $owner = User::factory()->create();
        $owner->givePermissionTo(['provider.products.create', 'provider.products.update']);
        $commerce = Commerce::factory()->create(['owner_user_id' => $owner->id]);
        $branch = CommerceBranch::factory()->create(['commerce_id' => $commerce->id]);

        // Tres componentes al mismo precio: el prorrateo (100/300 = 0.3333...)
        // no cae en un número exacto de centavos por componente.
        $components = collect(range(1, 3))->map(function () use ($commerce, $branch) {
            $component = Product::factory()->create([
                'commerce_id' => $commerce->id,
                'original_price' => 100,
                'discounted_price' => 100,
            ]);
            $component->commerceBranches()->attach($branch->id, ['quantity_available' => 10, 'is_published' => true]);

            return $component;
        });

        $pack = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'product_type' => Constant::PRODUCT_TYPE_PACKAGE,
            'original_price' => 300,
            'discounted_price' => 100,
        ]);
        foreach ($components as $component) {
            $pack->packageItems()->attach($component->id, ['quantity' => 1]);
        }
        $pack->commerceBranches()->attach($branch->id, ['quantity_available' => 5, 'is_published' => true]);

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 1],
            ],
        ]);
        $response->assertCreated();

        $order = Order::latest('id')->first();
        $childrenTotal = $order->allItems()
            ->whereNotNull('parent_package_id')
            ->get()
            ->sum(fn ($item) => $item->quantity * $item->unit_price);

        // Sin la corrección de residuo, la suma naive de 3 * round(33.333, 2)
        // da 99.99, no 100.00.
        $this->assertSame(100.0, $childrenTotal);
    }

    public function test_historical_order_items_are_unaffected_by_later_changes_to_package_composition(): void
    {
        ['owner' => $owner, 'commerce' => $commerce, 'branch' => $branch, 'componentA' => $componentA, 'componentB' => $componentB, 'pack' => $pack] =
            $this->buildPackWithTwoComponents();

        $customer = User::factory()->create();
        $customer->assignRole('user');
        $customer->givePermissionTo('customer.orders.create');
        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/orders', [
            'commerce_branch_id' => $branch->id,
            'items' => [
                ['product_id' => $pack->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $order = Order::latest('id')->first();

        // El aliado cambia la composición del pack: saca B, mete C.
        Sanctum::actingAs($owner);
        $componentC = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'original_price' => 15000,
            'discounted_price' => 12000,
        ]);
        $componentC->commerceBranches()->attach($branch->id, ['quantity_available' => 20, 'is_published' => true]);
        $pack->packageItems()->sync([
            $componentA->id => ['quantity' => 1],
            $componentC->id => ['quantity' => 1],
        ]);

        // La orden ya vendida sigue mostrando lo que realmente se vendió.
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $componentB->id,
            'parent_package_id' => $pack->id,
        ]);
        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
            'product_id' => $componentC->id,
        ]);
    }
}
