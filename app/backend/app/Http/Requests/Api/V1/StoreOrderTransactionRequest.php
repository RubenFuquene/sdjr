<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Order;
use App\Payments\Gateways\FakePaymentGateway;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreOrderTransactionRequest",
 *
 *     @OA\Property(property="payment_method_id", type="integer", nullable=true, example=1, description="Metodo de pago tokenizado del usuario (opcional)"),
 *     @OA\Property(property="simulate", type="string", nullable=true, enum={"reject"}, description="Solo pasarela fake: fuerza el rechazo determinista para QA. Una pasarela real ignora este campo.")
 * )
 */
class StoreOrderTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! $user->can('customer.orders.pay')) {
            return false;
        }

        // Solo el dueño de la orden puede pagarla. El id viene de la ruta,
        // nunca del body.
        $orderId = (int) ($this->route('id') ?? 0);
        if ($orderId <= 0) {
            return false;
        }

        return Order::query()
            ->whereKey($orderId)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => [
                'nullable',
                'integer',
                // El método debe pertenecer al usuario autenticado.
                'exists:payment_methods,id,user_id,'.$this->user()->id,
            ],
            'simulate' => ['nullable', 'string', 'in:'.FakePaymentGateway::SIMULATE_REJECT],
        ];
    }
}
