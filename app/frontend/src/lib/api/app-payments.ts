import { ApiError, fetchWithErrorHandling } from "./client";
import type { AppTransaction, PayOrderPayload, PayOrderResult } from "@/types/app-payments";

interface TransactionEnvelope {
  status: boolean;
  message: string | null;
  data: AppTransaction;
}

function extractTransactionFromErrorData(data: unknown): AppTransaction | null {
  if (data && typeof data === "object" && "data" in data) {
    const inner = (data as { data: unknown }).data;
    if (inner && typeof inner === "object" && "status" in inner && "order_id" in inner) {
      return inner as AppTransaction;
    }
  }

  return null;
}

/**
 * POST /api/v1/orders/{id}/transactions
 * Cobra la orden vía la pasarela activa del backend.
 *
 * El rechazo de la pasarela (HTTP 402) NO se propaga como excepción: es un
 * resultado de negocio que la UI muestra (mensaje + reintento). Solo los
 * errores reales (orden no pagable 422, sin permiso 403, red) lanzan ApiError.
 *
 * @throws {ApiError} para errores distintos al rechazo de la pasarela.
 */
export async function payOrder(orderId: number, payload: PayOrderPayload = {}): Promise<PayOrderResult> {
  if (!Number.isInteger(orderId) || orderId <= 0) {
    throw new ApiError("Id de orden inválido.", 422);
  }

  try {
    const response = await fetchWithErrorHandling<TransactionEnvelope>(
      `/api/v1/orders/${orderId}/transactions`,
      {
        method: "POST",
        body: JSON.stringify(payload),
      }
    );

    return { approved: true, transaction: response.data };
  } catch (error) {
    if (error instanceof ApiError && error.status === 402) {
      const transaction = extractTransactionFromErrorData(error.data);

      return {
        approved: false,
        transaction,
        reason: transaction?.failure_reason ?? "El pago no fue aprobado por la pasarela.",
      };
    }

    throw error;
  }
}
