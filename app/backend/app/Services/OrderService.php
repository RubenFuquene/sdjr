<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

            return $order->load('items');
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
