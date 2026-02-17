/**
 * Commerces (Proveedores) API Module
 * GET list of commerces
 */

import { ApiResponse, CommerceFromAPI } from "@/types/admin";
import type { ProveedorPayload } from "@/types/provider";
import { fetchWithErrorHandling } from "./client";

export interface GetCommercesParams {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string; // optional: activo/inactivo o 0/1 según backend
}

/**
 * GET /api/v1/commerces
 * Obtiene listado paginado de comercios/proveedores
 */
export async function getCommerces({
  page = 1,
  perPage = 15,
  search,
  status,
}: GetCommercesParams = {}): Promise<ApiResponse<CommerceFromAPI>> {
  const params = new URLSearchParams();
  params.set("page", String(page));
  params.set("per_page", String(perPage));
  if (search) params.set("search", search);
  if (status) params.set("status", status);

  return fetchWithErrorHandling<ApiResponse<CommerceFromAPI>>(
    `/api/v1/commerces?${params.toString()}`
  );
}

// ============================================
// Detail & Update endpoints
// ============================================

/**
 * Estructura genérica de respuesta simple del backend (no paginada)
 * successResponse: { status: true, message?: string, data: T }
 */
export interface ApiSuccess<T> {
  status: boolean;
  message?: string;
  data: T;
}

/**
 * POST /api/v1/commerces
 * Crea un nuevo comercio/proveedor
 */
export async function createCommerce(
  payload: ProveedorPayload
): Promise<ApiSuccess<CommerceFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI>>(
    `/api/v1/commerces`,
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );
}

/**
 * GET /api/v1/commerces/{id}
 * Obtiene el detalle de un comercio/proveedor
 */
export async function getCommerceById(id: number): Promise<ApiSuccess<CommerceFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI>>(
    `/api/v1/commerces/${id}`
  );
}

/**
 * PUT /api/v1/commerces/{id}
 * Actualiza un comercio/proveedor
 * Payload debe seguir la estructura esperada por CommerceRequest
 */
export async function updateCommerce(
  id: number,
  payload: Record<string, unknown>
): Promise<ApiSuccess<CommerceFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI>>(
    `/api/v1/commerces/${id}`,
    {
      method: "PUT",
      body: JSON.stringify(payload),
    }
  );
}

/**
 * DELETE /api/v1/commerces/{id}
 * Elimina un comercio/proveedor (soft delete)
 * Retorna 204 No Content en caso de éxito
 */
export async function deleteCommerce(id: number): Promise<void> {
  await fetchWithErrorHandling<void>(
    `/api/v1/commerces/${id}`,
    {
      method: "DELETE",
    }
  );
}
