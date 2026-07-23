<?php

declare(strict_types=1);

namespace App\Payments;

/**
 * Intención de cobro que el dominio entrega a la pasarela.
 *
 * DTO inmutable: el gateway recibe exactamente lo que necesita para autorizar,
 * nada más. El monto SIEMPRE proviene de la orden en backend (nunca del
 * cliente); PaymentService es el único constructor legítimo de este objeto.
 *
 * `options` transporta banderas específicas de una implementación (ej. el
 * `simulate` del FakePaymentGateway). Las pasarelas ignoran las opciones que
 * no reconocen: nada de esto contamina el dominio.
 */
final readonly class PaymentIntent
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public int $orderId,
        public float $amount,
        public string $currency,
        public ?int $paymentMethodId = null,
        public array $options = [],
    ) {}
}
