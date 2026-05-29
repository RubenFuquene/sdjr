/**
 * Commerces (Proveedores) API Module
 * GET list of commerces
 */

import { ApiResponse } from "@/types/admin";
import type {
  CommerceBasicDataResponse,
  CommerceFromAPI,
  CommerceVerificationStatus,
  ProveedorPayload,
  CommerceBasicPayload,
} from "@/types/commerces";
import { fetchWithErrorHandling } from "./client";
import type { ApiSuccess } from "./types";

export interface GetCommercesParams {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string; // optional: activo/inactivo o 0/1 según backend
  verified?: string; // optional: 1 (verified) | 0 (not verified) | all
}

export type CommerceCommentType = "PR" | "SU" | "IN" | "VA" | "RJ";

export interface CommerceCommentFromAPI {
  id: number;
  commerce_id: number;
  created_by: number;
  comment: string;
  priority_type_id: number;
  priority_type: {
    code: string | null;
    name: string | null;
  };
  comment_type: {
    code: string;
    name: string | null;
  };
  color: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface GetCommerceCommentsParams {
  page?: number;
  perPage?: number;
  createdBy?: number;
  priority?: string;
  status?: string;
}

export interface CreateCommerceCommentPayload {
  comment: string;
  priority_type_id: number;
  comment_type: CommerceCommentType;
  color?: string;
  status?: string;
  created_by?: number;
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
 * Backend implementado ✓
 * 
 * @returns Commerce del usuario o null si no tiene comercio registrado
 */
export async function getMyCommerce(): Promise<ApiSuccess<CommerceFromAPI | null>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI | null>>(
    `/api/v1/me/commerce`
  );
}

// ============================================
// Terms & Conditions Endpoints
// ============================================

export interface AcceptCommerceTermsPayload {
  terms_accepted_version: number;
}

/**
 * PATCH /api/v1/commerces/{id}/accept-terms
 * Marca los términos y condiciones como aceptados por el proveedor
 * 
 * @param id - ID del comercio
 * @param payload - { terms_accepted_version: number >= 1 }
 * @returns Comercio actualizado con terms_accepted_at y terms_accepted_version
 */
export async function acceptCommerceTerms(
  id: number,
  payload: AcceptCommerceTermsPayload
): Promise<ApiSuccess<CommerceFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI>>(
    `/api/v1/commerces/${id}/accept-terms`,
    {
      method: "PATCH",
      body: JSON.stringify(payload),
    }
  );
}

// ============================================
// Verification & Status Endpoints
// ============================================

/**
 * PATCH /api/v1/commerces/{id}/verification
 * Actualiza el estado de verificación de un comercio
 * 
 * Pendiente: is_verified = 0
 * Aprobación: is_verified = 1
 * Rechazo: is_verified = 2
 * 
 * @param id - ID del comercio
 * @param isVerified - 0 pendiente, 1 aprobar, 2 rechazar
 * @returns Comercio actualizado
 */
export async function updateCommerceVerification(
  id: number,
  isVerified: CommerceVerificationStatus,
  message: string
): Promise<ApiSuccess<CommerceFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceFromAPI>>(
    `/api/v1/commerces/${id}/verification`,
    {
      method: "PATCH",
      body: JSON.stringify({ is_verified: isVerified, message }),
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
 * Equivalente a: updateCommerceVerification(id, 1, message)
 * 
 * @param id - ID del comercio
 * @param message - Mensaje de aprobación (mín. 10 caracteres)
 * @returns Comercio actualizado
 */
export async function approveCommerce(
  id: number,
  message: string = 'Su registro como proveedor ha sido aprobado satisfactoriamente.'
): Promise<ApiSuccess<CommerceFromAPI>> {
  return updateCommerceVerification(id, 1, message);
}

/**
 * Convenience function: Rechazar un comercio/proveedor
 * Equivalente a: updateCommerceVerification(id, 2, message)
 * 
 * @param id - ID del comercio
 * @param message - Motivo del rechazo (mín. 10 caracteres, máx. 500)
 * @returns Comercio actualizado
 */
export async function rejectCommerce(
  id: number,
  message: string
): Promise<ApiSuccess<CommerceFromAPI>> {
  return updateCommerceVerification(id, 2, message);
}

// ============================================
// Commerce Comments Endpoints
// ============================================

/**
 * GET /api/v1/commerces/{commerceId}/comments
 * Obtiene comentarios paginados de un comercio
 */
export async function getCommerceComments(
  commerceId: number,
  {
    page = 1,
    perPage = 15,
    createdBy,
    priority,
    status,
  }: GetCommerceCommentsParams = {}
): Promise<ApiResponse<CommerceCommentFromAPI>> {
  const params = new URLSearchParams();
  params.set("page", String(page));
  params.set("per_page", String(perPage));
  if (createdBy !== undefined) params.set("created_by", String(createdBy));
  if (priority) params.set("priority", priority);
  if (status) params.set("status", status);

  return fetchWithErrorHandling<ApiResponse<CommerceCommentFromAPI>>(
    `/api/v1/commerces/${commerceId}/comments?${params.toString()}`
  );
}

/**
 * POST /api/v1/commerces/{commerceId}/comments
 * Crea un comentario para un comercio
 */
export async function createCommerceComment(
  commerceId: number,
  payload: CreateCommerceCommentPayload
): Promise<ApiSuccess<CommerceCommentFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<CommerceCommentFromAPI>>(
    `/api/v1/commerces/${commerceId}/comments`,
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );
}