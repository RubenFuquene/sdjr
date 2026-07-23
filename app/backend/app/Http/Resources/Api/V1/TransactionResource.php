<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TransactionResource",
 *     type="object",
 *     title="Transaction Resource",
 *     description="Cargo/pago de una orden. El payload crudo del gateway NO se expone: es trazabilidad interna.",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=10),
 *     @OA\Property(property="payment_method_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="provider", type="string", example="fake"),
 *     @OA\Property(property="external_id", type="string", nullable=true, example="fake_01HXX..."),
 *     @OA\Property(property="status", type="string", enum={"initiated","approved","rejected","failed"}, example="approved"),
 *     @OA\Property(property="amount", type="number", format="float", example=16000),
 *     @OA\Property(property="currency", type="string", example="COP"),
 *     @OA\Property(property="failure_reason", type="string", nullable=true, example="Pago rechazado por la entidad emisora (simulado)."),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_method_id' => $this->payment_method_id,
            'provider' => $this->provider,
            'external_id' => $this->external_id,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            // Único dato del payload que el cliente necesita; el resto del
            // payload crudo del gateway queda fuera de la respuesta a propósito.
            'failure_reason' => $this->payload['failure_reason'] ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
