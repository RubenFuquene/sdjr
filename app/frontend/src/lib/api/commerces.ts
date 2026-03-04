/**
 * Commerces (Proveedores) API Module
 * GET list of commerces
 */

import { ApiResponse } from "@/types/admin";
import type { CommerceFromAPI, ProveedorPayload, CommerceBasicPayload, CommerceBasicDataResponse } from "@/types/commerces";
import { fetchWithErrorHandling } from "./client";

export interface GetCommercesParams {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string; // optional: activo/inactivo o 0/1 según backend
  verified?: string; // optional: 1 (verified) | 0 (not verified) | all
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
  verified,
}: GetCommercesParams = {}): Promise<ApiResponse<CommerceFromAPI>> {
  const params = new URLSearchParams();
  params.set("page", String(page));
  params.set("per_page", String(perPage));
  if (search) params.set("search", search);
  if (status) params.set("status", status);
  if (verified) params.set("verified", verified);

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
 * ⚠️ SOLO PARA PANEL ADMINISTRATIVO
 * Crea un nuevo comercio/proveedor desde el panel admin
 * 
 * Para registro de proveedores, usar createCommerceBasic()
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
 * POST /api/v1/commerces/basic
 * Crea un nuevo comercio con datos básicos desde REGISTRO DE PROVEEDOR
 * Incluye: comercio, representante legal, documentos y cuenta bancaria
 * 
 * Retorna CommerceBasicDataResponse con estructura específica del endpoint
 */
export async function createCommerceBasic(
  payload: CommerceBasicPayload
): Promise<ApiSuccess<CommerceBasicDataResponse>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceBasicDataResponse>>(
    `/api/v1/commerces/basic`,
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

/**
 * GET /api/v1/me/commerce
 * Obtiene el comercio del usuario autenticado (por owner_user_id)
 * 
 * ⚠️ PENDIENTE IMPLEMENTACIÓN BACKEND
 * Ver: docs/backend-endpoints-v3.md sección D
 * 
 * @returns Commerce del usuario o null si no tiene comercio registrado
 */
export async function getMyCommerce(): Promise<ApiSuccess<CommerceFromAPI | null>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI | null>>(
    `/api/v1/me/commerce`
  );
}
// ============================================
// Verification & Status Endpoints
// ============================================

/**
 * PATCH /api/v1/commerces/{id}/verification
 * Actualiza el estado de verificación de un comercio
 * 
 * Aprobación: is_verified = 1
 * Rechazo: is_verified = 0
 * 
 * @param id - ID del comercio
 * @param isVerified - 1 para aprobar, 0 para rechazar
 * @returns Comercio actualizado
 */
export async function updateCommerceVerification(
  id: number,
  isVerified: 0 | 1
): Promise<ApiSuccess<CommerceFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI>>(
    `/api/v1/commerces/${id}/verification`,
    {
      method: "PATCH",
      body: JSON.stringify({ is_verified: isVerified }),
    }
  );
}

/**
 * PATCH /api/v1/commerces/{id}/status
 * Actualiza el estado activo/inactivo de un comercio
 * 
 * Activar: is_active = 1
 * Desactivar: is_active = 0
 * 
 * @param id - ID del comercio
 * @param isActive - 1 para activar, 0 para desactivar
 * @returns Comercio actualizado
 */
export async function updateCommerceStatus(
  id: number,
  isActive: 0 | 1
): Promise<ApiSuccess<CommerceFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI>>(
    `/api/v1/commerces/${id}/status`,
    {
      method: "PATCH",
      body: JSON.stringify({ is_active: isActive }),
    }
  );
}

/**
 * Convenience function: Aprobar un comercio/proveedor
 * Equivalente a: updateCommerceVerification(id, 1)
 * 
 * @param id - ID del comercio
 * @returns Comercio actualizado
 */
export async function approveCommerce(id: number): Promise<ApiSuccess<CommerceFromAPI>> {
  return updateCommerceVerification(id, 1);
}

/**
 * Convenience function: Rechazar un comercio/proveedor
 * Equivalente a: updateCommerceVerification(id, 0)
 * 
 * @param id - ID del comercio
 * @returns Comercio actualizado
 */
export async function rejectCommerce(id: number): Promise<ApiSuccess<CommerceFromAPI>> {
  return updateCommerceVerification(id, 0);
}