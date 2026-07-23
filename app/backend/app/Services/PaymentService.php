<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Contracts\PaymentGatewayInterface;
use App\Enums\TransactionStatus;
use App\Models\Order;
use App\Models\Transaction;
use App\Payments\PaymentIntent;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orquesta el cobro de una orden, desacoplado del dominio de órdenes.
 *
 * Principios:
 *  - Depende del contrato PaymentGatewayInterface (DIP): la pasarela activa
 *    la decide config('payments.gateway'), no este servicio.
 *  - El monto SIEMPRE se toma de la orden en backend, jamás del cliente.
 *  - La confirmación de la orden se DELEGA a OrderService (única fuente de
 *    verdad de transiciones de estado y descuento de stock).
 *  - Idempotencia: una orden con transacción approved no admite otro cobro.
 */
class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly OrderService $orderService,
    ) {}

    /**
     * Cobra una orden pending. Devuelve la transacción resultante
     * (approved, rejected o failed — el rechazo NO es excepción).
     *
     * @param  array<string, mixed>  $options  Banderas específicas de gateway (ej. simulate=reject en fake).
     *
     * @throws DomainException si la orden no es pagable (no pending, o ya cobrada).
     */
    public function pay(Order $order, array $options = [], ?int $paymentMethodId = null): Transaction
    {
        return DB::transaction(function () use ($order, $options, $paymentMethodId) {
            // Lock pesimista: dos submits simultáneos del mismo pago se serializan
            // y el segundo verá el estado ya confirmado / la transacción approved.
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            $this->assertPayable($order);

            $transaction = Transaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethodId,
                'provider' => (string) config('payments.gateway'),
                'status' => TransactionStatus::Initiated,
                'amount' => $order->total_price,
                'currency' => (string) config('payments.currency', 'COP'),
            ]);

            $result = $this->gateway->authorize(new PaymentIntent(
                orderId: $order->id,
                amount: (float) $order->total_price,
                currency: $transaction->currency,
                paymentMethodId: $paymentMethodId,
                options: $options,
            ));

            $transaction->external_id = $result->externalId;
            $transaction->status = $result->status;
            $transaction->payload = array_merge($result->rawPayload, array_filter([
                'failure_reason' => $result->failureReason,
            ]));
            $transaction->save();

            if ($result->isApproved()) {
                // Delegado a OrderService: reutiliza validateStatusTransition
                // y el descuento de stock existente. No se duplica aquí.
                $this->orderService->patchStatus($order->id, Constant::ORDER_STATUS_CONFIRMED);
            } else {
                Log::info('Payment not approved', [
                    'order_id' => $order->id,
                    'transaction_id' => $transaction->id,
                    'status' => $result->status->value,
                    'reason' => $result->failureReason,
                ]);
            }

            return $transaction->refresh();
        });
    }

    /**
     * Una orden es pagable solo si está pending y no tiene ya un cobro aprobado.
     *
     * @throws DomainException
     */
    protected function assertPayable(Order $order): void
    {
        if ($order->status !== Constant::ORDER_STATUS_PENDING) {
            throw new DomainException('Order is not payable in its current status');
        }

        $alreadyPaid = $order->transactions()
            ->where('status', TransactionStatus::Approved->value)
            ->exists();

        if ($alreadyPaid) {
            throw new DomainException('Order already has an approved transaction');
        }
    }
}
