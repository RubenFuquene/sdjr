<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;

/**
 * SCRUM-376: un producto (o un componente de pack) no tiene clasificación
 * fiscal válida (otro_verificar o NULL) y por tanto no puede venderse — la
 * FAN nunca podría emitirse desde esta línea. El mensaje es neutro de cara
 * al comprador (D5 del plan de SCRUM-376): no expone que el motivo es
 * fiscal, un problema interno del aliado que el comprador no puede
 * resolver. El detalle real se registra en log antes de lanzar esta
 * excepción.
 */
class ProductFiscalClassificationUnavailableException extends OrderItemRejectedException
{
    public function __construct(Product $product)
    {
        parent::__construct(__('orders.fiscal_classification_unavailable', ['title' => $product->title]));
    }
}
