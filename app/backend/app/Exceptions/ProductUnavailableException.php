<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;

/**
 * SCRUM-375: el producto (o pack) no cumple alguna de las compuertas que el
 * catálogo sí aplica para mostrarlo — despublicado en la sede, inactivo, o
 * con clasificación vencida (`expires_at`). Mensaje único y neutro (D3 del
 * plan): al comprador le da igual cuál de las tres causas aplicó, para él
 * el producto "no está disponible". El detalle real se registra en log
 * antes de lanzar esta excepción.
 */
class ProductUnavailableException extends OrderItemRejectedException
{
    public function __construct(?Product $product = null)
    {
        parent::__construct($product
            ? __('orders.product_unavailable', ['title' => $product->title])
            : __('orders.product_unavailable_generic'));
    }
}
