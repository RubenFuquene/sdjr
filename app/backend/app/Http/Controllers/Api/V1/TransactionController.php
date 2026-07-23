<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderTransactionRequest;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Models\Order;
use App\Payments\Gateways\FakePaymentGateway;
use App\Services\PaymentService;
use App\Traits\ApiResponseTrait;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Cobro de órdenes (transacciones de pago del comprador)"
 * )
 */
class TransactionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * @OA\Post(
     *   path="/api/v1/orders/{id}/transactions",
     *   tags={"Transactions"},
     *   summary="Pagar una orden (crea una transacción de cobro)",
     *   description="Cobra la orden vía la pasarela activa (config payments.gateway). Aprobado: la orden pasa a confirmed. Rechazado: la transacción queda rejected y la orden sigue pending.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/StoreOrderTransactionRequest")),
     *
     *   @OA\Response(response=201, description="Pago aprobado", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/TransactionResource"))),
     *   @OA\Response(response=402, description="Pago rechazado por la pasarela", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/TransactionResource"))),
     *   @OA\Response(response=403, description="Sin permiso o no es dueño de la orden"),
     *   @OA\Response(response=404, description="Orden no encontrada"),
     *   @OA\Response(response=422, description="La orden no es pagable (no pending o ya cobrada)")
     * )
     */
    public function store(StoreOrderTransactionRequest $request, int $id): JsonResponse
    {
        try {
            // La existencia + ownership ya la validó el Request; findOrFail
            // cubre la ventana entre ambas consultas.
            $order = Order::findOrFail($id);
            $validated = $request->validated();

            $options = array_filter([
                FakePaymentGateway::SIMULATE_OPTION => $validated['simulate'] ?? null,
            ]);

            $transaction = $this->paymentService->pay(
                $order,
                $options,
                isset($validated['payment_method_id']) ? (int) $validated['payment_method_id'] : null,
            );

            if ($transaction->status->value === 'approved') {
                return $this->createdResponse(new TransactionResource($transaction), 'Payment approved');
            }

            // Rechazo de negocio de la pasarela: la transacción existe (auditoría)
            // pero el cobro no ocurrió. 402 Payment Required comunica el fallo.
            return response()->json([
                'status' => false,
                'message' => 'Payment was not approved',
                'data' => new TransactionResource($transaction),
            ], Response::HTTP_PAYMENT_REQUIRED);
        } catch (DomainException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            Log::error('Payment processing failed', ['order_id' => $id, 'error' => $e->getMessage()]);

            return $this->errorResponse('Error processing payment', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
