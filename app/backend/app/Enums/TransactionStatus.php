<?php

namespace App\Enums;

/**
 * Estado de una transacción de pago (cargo al comprador).
 *
 * Dimensión independiente del estado de la orden: una orden pending puede
 * acumular transacciones rejected/failed; solo una approved dispara la
 * confirmación de la orden (vía OrderService, no desde aquí).
 */
enum TransactionStatus: string
{
    case Initiated = 'initiated';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Failed = 'failed';

    /**
     * Un estado final ya no cambia: la transacción no se reintenta,
     * se crea una nueva si el usuario vuelve a intentar pagar.
     */
    public function isFinal(): bool
    {
        return $this !== self::Initiated;
    }
}
