import { ApiError, fetchWithErrorHandling } from "./client";
import type { CreateOrderPayload, CreateOrderResponse } from "@/types/app-orders";

/**
 * Valida el payload antes de gastar una llamada de red, siguiendo el mismo
 * patrón que `ensureValidCoordinates` en app-catalog.ts.
 *
 * @throws {ApiError} 422 si el payload es inválido.
 */
function ensureValidOrderPayload(payload: CreateOrderPayload): void {
  if (!Number.isInteger(payload.commerce_branch_id) || payload.commerce_branch_id <= 0) {
    throw new ApiError("Sucursal inválida para crear la orden.", 422);
  }

  if (!Array.isArray(payload.items) || payload.items.length === 0) {
    throw new ApiError("La orden debe incluir al menos un producto.", 422);
  }

  const hasInvalidItem = payload.items.some(
    (item) =>
      !Number.isInteger(item.product_id) ||
      item.product_id <= 0 ||
      !Number.isInteger(item.quantity) ||
      item.quantity < 1
  );

  if (hasInvalidItem) {
    throw new ApiError("Cada producto debe tener id válido y cantidad mínima de 1.", 422);
  }
}

/**
 * POST /api/v1/orders
 * Crea una orden real para el usuario autenticado. El backend fija el `user_id`
 * desde el token; el cliente solo envía sucursal e ítems.
 *
 * Los errores llegan como `ApiError` tipado por status (403 sin permiso,
 * 422 validación/stock insuficiente); ver `isInsufficientStockError` para
 * distinguir el caso de stock.
 *
 * @throws {ApiError}
 */
export async function createOrder(payload: CreateOrderPayload): Promise<CreateOrderResponse> {
  ensureValidOrderPayload(payload);

  return fetchWithErrorHandling<CreateOrderResponse>("/api/v1/orders", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

/**
 * Distingue el 422 de stock insuficiente del resto de errores de validación,
 * para que la UI pueda mostrar un mensaje específico.
 *
 * El backend responde este caso con HTTP 422 y un mensaje que incluye
 * "not available in the requested quantity" (OrderController@store).
 */
export function isInsufficientStockError(error: unknown): error is ApiError {
  if (!(error instanceof ApiError) || error.status !== 422) {
    return false;
  }

  const raw = error.data;
  const backendMessage =
    raw && typeof raw === "object" && "message" in raw
      ? String((raw as { message: unknown }).message ?? "")
      : "";

  return backendMessage.toLowerCase().includes("not available in the requested quantity");
}
