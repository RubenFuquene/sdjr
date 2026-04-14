<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
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
                $price = $item['unit_price'] ?? $this->getProductPrice((int) $item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                ]);
                $total += $price * $item['quantity'];
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

            // Si la orden es confirmada, se debe reducir el stock de los productos correspondientes (si se implementa stock)
            // Extraer los productos de la orden y reducir su stock en consecuencia, asegurando que no se permita confirmar la orden si no hay suficiente stock disponible.
            if ($status === Constant::ORDER_STATUS_CONFIRMED) {
                app(ProductService::class)->dismissProductConfirmedStock($order);
            }

            $order->status = $status;
            $order->save();

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
     * Obtener el precio actual del producto
     * (Mock, debes ajustar si el modelo Product cambia o si usa "original_price")
     */
    protected function getProductPrice(int $productId): float
    {
        $product = Product::find($productId);

        return $product ? (float) ($product->original_price ?? $product->original_price) : 0.0;
    }
}
