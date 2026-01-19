/**
 * Users API Module
 * CRUD operations for user management
 */

import { fetchWithErrorHandling } from "./client";
import type { ApiResponse } from "@/types/admin";
import type {
  UserFromAPI,
  GetUsersParams,
  CreateUserPayload,
  UpdateUserPayload,
} from "@/types/user";

// ============================================
// Response Types - Specific to Users
// ============================================

/**
 * Respuesta simple del backend (no paginada)
 */
export interface ApiSuccess<T> {
  status: boolean;
  message?: string;
  data: T;
}

// ============================================
// List & Search - GET /api/v1/users
// ============================================

/**
 * GET /api/v1/users
 * Obtiene listado paginado de usuarios
 */
export async function getUsers({
  page = 1,
  perPage = 15,
  search,
  role,
  status,
}: GetUsersParams = {}): Promise<ApiResponse<UserFromAPI>> {
  const params = new URLSearchParams();
  params.set("page", String(page));
  params.set("per_page", String(perPage));
  if (search) params.set("search", search);
  if (role && role !== 'todos') params.set("role", role);
  if (status) params.set("status", status);

  return fetchWithErrorHandling<ApiResponse<UserFromAPI>>(
    `/api/v1/users?${params.toString()}`
  );
}

// ============================================
// Detail - GET /api/v1/users/{id}
// ============================================

/**
 * GET /api/v1/users/{id}
 * Obtiene el detalle de un usuario
 */
export async function getUserById(id: number): Promise<ApiSuccess<UserFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<UserFromAPI>>(
    `/api/v1/users/${id}`
  );
}

// ============================================
// Create - POST /api/v1/users
// ============================================

/**
 * POST /api/v1/users
 * Crea un nuevo usuario
 */
export async function createUser(
  payload: CreateUserPayload
): Promise<ApiSuccess<UserFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<UserFromAPI>>(
    `/api/v1/users`,
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );
}

// ============================================
// Update - PUT /api/v1/users/{id}
// ============================================

/**
 * PUT /api/v1/users/{id}
 * Actualiza un usuario existente
 */
export async function updateUser(
  id: number,
  payload: UpdateUserPayload
): Promise<ApiSuccess<UserFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<UserFromAPI>>(
    `/api/v1/users/${id}`,
    {
      method: "PUT",
      body: JSON.stringify(payload),
    }
  );
}

// ============================================
// Toggle Status - PATCH /api/v1/users/{id}/status
// ============================================

/**
 * PATCH /api/v1/users/{id}/status
 * Activa o desactiva un usuario
 */
export async function updateUserStatus(
  id: number,
  status: '1' | '0'
): Promise<ApiSuccess<UserFromAPI>> {
  return fetchWithErrorHandling<ApiSuccess<UserFromAPI>>(
    `/api/v1/users/${id}/status`,
    {
      method: "PATCH",
      body: JSON.stringify({ status }),
    }
  );
}

// ============================================
// Delete - DELETE /api/v1/users/{id}
// ============================================

/**
 * DELETE /api/v1/users/{id}
 * Elimina un usuario (soft delete)
 * Retorna 200 OK (no 204 como commerces)
 */
export async function deleteUser(id: number): Promise<void> {
  await fetchWithErrorHandling<void>(
    `/api/v1/users/${id}`,
    {
      method: "DELETE",
    }
  );
}

// ============================================
// Administrators - GET /api/v1/administrators
// ============================================

/**
 * GET /api/v1/administrators
 * Obtiene listado paginado de usuarios administradores
 */
export async function getAdministrators({
  page = 1,
  perPage = 15,
}: GetUsersParams = {}): Promise<ApiResponse<UserFromAPI>> {
  const params = new URLSearchParams();
  params.set("page", String(page));
  params.set("per_page", String(perPage));

  return fetchWithErrorHandling<ApiResponse<UserFromAPI>>(
    `/api/v1/administrators?${params.toString()}`
  );
}
