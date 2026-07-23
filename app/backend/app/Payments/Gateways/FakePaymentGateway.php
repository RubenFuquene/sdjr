<?php

declare(strict_types=1);

namespace App\Payments\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\TransactionStatus;
use App\Payments\PaymentIntent;
use App\Payments\PaymentResult;
use Illuminate\Support\Str;

/**
 * Pasarela simulada para desarrollo y QA (no hay proveedor real contratado).
 *
 * Comportamiento determinista, sin aleatoriedad:
 *  - Aprueba todo cobro por defecto.
 *  - Rechaza si la intención trae options['simulate'] === 'reject' — el
 *    disparador que QA usa para validar el camino de fallo. Una pasarela
 *    real simplemente ignora esa opción.
 */
class FakePaymentGateway implements PaymentGatewayInterface
{
    public const SIMULATE_OPTION = 'simulate';

    public const SIMULATE_REJECT = 'reject';

    public function authorize(PaymentIntent $intent): PaymentResult
    {
        // Latencia mínima simulada para que la UI ejercite su estado "procesando".
        usleep(150_000);

        if (($intent->options[self::SIMULATE_OPTION] ?? null) === self::SIMULATE_REJECT) {
            return new PaymentResult(
                status: TransactionStatus::Rejected,
                externalId: $this->fakeReference(),
                failureReason: 'Pago rechazado por la entidad emisora (simulado).',
                rawPayload: [
                    'gateway' => 'fake',
                    'simulated' => true,
                    'outcome' => 'rejected',
                ],
            );
        }

        return new PaymentResult(
            status: TransactionStatus::Approved,
            externalId: $this->fakeReference(),
            rawPayload: [
                'gateway' => 'fake',
                'simulated' => true,
                'outcome' => 'approved',
            ],
        );
    }

    public function status(string $providerReference): PaymentResult
    {
        // La pasarela fake es síncrona: todo cobro quedó resuelto en authorize().
        return new PaymentResult(
            status: TransactionStatus::Approved,
            externalId: $providerReference,
            rawPayload: ['gateway' => 'fake', 'simulated' => true],
        );
    }

    private function fakeReference(): string
    {
        return 'fake_'.Str::ulid();
    }
}
