<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;

/**
 * SCRUM-375: el pivote producto-sede no existe o no tiene stock suficiente
 * para la cantidad pedida. Antes de esta tarea el rechazo era un simple
 * `return false` con un mensaje genérico hardcodeado en inglés en
 * OrderController — unificado al mismo contrato de excepción que los demás
 * motivos de rechazo de un ítem de orden (D5 del plan).
 */
class InsufficientStockException extends OrderItemRejectedException
{
    public function __construct(Product $product)
    {
        parent::__construct(__('orders.insufficient_stock', ['title' => $product->title]));
    }
}
