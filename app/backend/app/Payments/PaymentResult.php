<?php

declare(strict_types=1);

namespace App\Payments;

use App\Enums\TransactionStatus;

/**
 * Resultado que la pasarela devuelve al dominio.
 *
 * DTO inmutable. `rawPayload` es la respuesta cruda del proveedor y se
 * persiste SOLO para trazabilidad interna (transactions.payload); nunca se
 * expone al cliente (ver TransactionResource).
 */
final readonly class PaymentResult
{
    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function __construct(
        public TransactionStatus $status,
        public ?string $externalId = null,
        public ?string $failureReason = null,
        public array $rawPayload = [],
    ) {}

    public function isApproved(): bool
    {
        return $this->status === TransactionStatus::Approved;
    }
}
