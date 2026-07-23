<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Payments\PaymentIntent;
use App\Payments\PaymentResult;

/**
 * Contrato de pasarela de pagos (patrón plug-in).
 *
 * Este es el primer contrato con inversión de dependencia del proyecto:
 * el dominio (PaymentService) depende de esta interfaz, nunca de una
 * implementación concreta. La implementación activa se resuelve desde
 * config('payments.gateway') en PaymentServiceProvider.
 *
 * Para enchufar una pasarela real (Wompi, Stripe, etc.):
 *   1. Crear una clase en App\Payments\Gateways que implemente esta interfaz.
 *   2. Registrarla en el map de config/payments.php.
 *   3. Cambiar PAYMENTS_GATEWAY en el entorno.
 * Ningún archivo de dominio, órdenes, HTTP ni frontend cambia.
 */
interface PaymentGatewayInterface
{
    /**
     * Autoriza (y en pasarelas síncronas, resuelve) el cobro de una intención.
     * Nunca lanza por rechazo de negocio: un rechazo es un PaymentResult con
     * status Rejected/Failed. Las excepciones quedan para fallas de
     * infraestructura (red, credenciales).
     */
    public function authorize(PaymentIntent $intent): PaymentResult;

    /**
     * Consulta el estado de una transacción por la referencia del proveedor.
     * Pensado para pasarelas asíncronas futuras (confirmación diferida).
     */
    public function status(string $providerReference): PaymentResult;
}
