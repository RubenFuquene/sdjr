<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Enums\FiscalCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class OrderService
{
    public function __construct(
        private readonly PackagePriceProrationService $packagePriceProrationService
    ) {}

    /**
     * Obtener listado filtrado de órdenes.
     */
    public function index(array $filters = []): Collection
    {
        $query = Order::query();
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['commerce_branch_id'])) {
            $query->where('commerce_branch_id', (int) $filters['commerce_branch_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with('items')->latest()->get();
    }

    /**
     * Crear una nueva orden con transacción.
     */
    public function store(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id' => $data['user_id'],
                'commerce_branch_id' => $data['commerce_branch_id'],
                'total_price' => 0, // Será actualizado abajo
                'status' => $data['status'] ?? 'pending',
            ]);
            $total = 0;
            foreach ($data['items'] as $item) {
                $product = Product::find((int) $item['product_id']);
                $price = $item['unit_price'] ?? ($product ? $product->currentSalePrice() : 0.0);
                $isPackage = $product && $product->product_type === Constant::PRODUCT_TYPE_PACKAGE;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    // SCRUM-376 (D4): la línea padre de un pack nunca lleva
                    // snapshot fiscal propio — los packs no tienen
                    // fiscal_code propio (SCRUM-362), se facturan por sus
                    // líneas hijas de componente, cada una con el suyo.
                    ...($isPackage ? $this->fiscalSnapshotFor(null) : $this->fiscalSnapshotFor($product)),
                ]);
                $total += $price * $item['quantity'];

                // SCRUM-366/367: explota la venta de un pack en líneas hijas
                // de componente, al precio realmente cobrado por esta línea
                // (no el techo recalculado) — el padre sigue siendo el único
                // que suma al total y descuenta stock (Order::items()).
                if ($isPackage) {
                    foreach ($this->packagePriceProrationService->prorate($product, (float) $price, (int) $item['quantity']) as $line) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $line['product']->id,
                            'parent_package_id' => $product->id,
                            'quantity' => $line['quantity'],
                            'unit_price' => $line['unit_price'],
                            // SCRUM-376: snapshot del componente real, no del
                            // pack — es el que se factura.
                            ...$this->fiscalSnapshotFor($line['product']),
                        ]);
                    }
                }
            }
            $order->total_price = round($total, 2);
            $order->save();

            // Cargar relaciones necesarias para la notificación
            $order->load(['items.product', 'user', 'commerceBranch.commerce']);

            // Enviar notificación de orden creada sin bloquear el flujo
            try {
                Notification::send($order->user, new OrderCreatedNotification($order));
            } catch (Throwable $e) {
                Log::warning('Order created email dispatch failed', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $order;
        });
    }

    /**
     * Obtener una orden concreta
     */
    public function show(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    /**
     * Actualizar estado de una orden
     */
    public function update(int $id, array $data): ?Order
    {
        $order = Order::find($id);
        if (! $order) {
            return null;
        }
        $order->status = $data['status'] ?? $order->status;
        $order->save();

        // Si la orden es confirmada, se debe reducir el stock de los productos correspondientes (si se implementa stock)
        // Extraer los productos de la orden y reducir su stock en consecuencia, asegurando que no se permita confirmar la orden si no hay suficiente stock disponible.
        if ($order->status === Constant::ORDER_STATUS_CONFIRMED) {
            app(ProductService::class)->dismissProductConfirmedStock($order);
        }

        return $order->load('items');
    }

    /**
     * Actualizar únicamente el estado de una orden.
     *
     * @throws \DomainException
     */
    public function patchStatus(int $id, string $status): ?Order
    {
        try {
            $order = Order::find($id);
            if (! $order) {
                return null;
            }

            if (! $this->validateStatusTransition((string) $order->status, $status)) {
                throw new \DomainException('Invalid order status transition');
            }

            $order->status = $status;
            $order->save();

            // Si la orden es confirmada, se debe reducir el stock de los productos
            // correspondientes. El status se guarda ANTES de descontar (no después,
            // como estaba antes): SCRUM-361 introdujo consultas que cuentan reservas
            // de órdenes en estado `pending` (PackageCommitmentSyncService, vía
            // BranchAvailabilityCalculator) — si esta misma orden todavía figurara
            // como `pending` en BD al calcular, su propia reserva se restaría dos
            // veces (una por el descuento directo, otra por la cuenta de "pendientes").
            if ($status === Constant::ORDER_STATUS_CONFIRMED) {
                app(ProductService::class)->dismissProductConfirmedStock($order);
            }

            return $order->load('items');
        } catch (\DomainException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Borrado lógico (soft-delete)
     */
    public function destroy(int $id): bool
    {
        $order = Order::find($id);

        if (! $order) {
            return false;
        }

        // Cancelar la orden antes de eliminarla
        $order->status = Constant::ORDER_STATUS_CANCELLED;
        $order->save();

        return $order->delete();
    }

    /**
     * Órdenes por usuario
     */
    public function getByUser(int $userId): Collection
    {
        return Order::where('user_id', $userId)->with('items')->get();
    }

    /**
     * Órdenes por sucursal de comercio
     */
    public function getByCommerceBranch(int $branchId): Collection
    {
        return Order::where('commerce_branch_id', $branchId)->with('items')->get();
    }

    /**
     * Validar transiciones de estado
     */
    public function validateStatusTransition(string $from, string $to): bool
    {
        // Aquí puedes definir reglas de transición válidas por negocio
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        Log::info("Validating order status transition from '{$from}' to '{$to}'");
        Log::info('Is valid transition: '.(in_array($to, $validTransitions[$from] ?? [], true) ? 'true' : 'false'));

        return in_array($to, $validTransitions[$from] ?? [], true);
    }

    /**
     * Precio de venta vigente de un producto (SCRUM-366): el mismo que la
     * app le mostró al cliente antes de comprar, no el precio de lista.
     */
    protected function currentSalePrice(int $productId): float
    {
        $product = Product::find($productId);

        return $product ? $product->currentSalePrice() : 0.0;
    }

    /**
     * SCRUM-376: único punto que arma el snapshot fiscal de una línea de
     * orden, tomado del producto en el instante de la venta. No relee la
     * regla de FiscalCodeResolver ni deriva tarifas — copia lo que
     * ProductService ya calculó y persistió en el producto.
     *
     * @return array{fiscal_code: ?FiscalCode, vat_rate: ?float, applies_inc: ?bool, inc_rate: ?float}
     */
    private function fiscalSnapshotFor(?Product $product): array
    {
        if (! $product) {
            return [
                'fiscal_code' => null,
                'vat_rate' => null,
                'applies_inc' => null,
                'inc_rate' => null,
            ];
        }

        return [
            'fiscal_code' => $product->fiscal_code,
            'vat_rate' => $product->vat_rate,
            'applies_inc' => $product->applies_inc,
            'inc_rate' => $product->inc_rate,
        ];
    }
}
