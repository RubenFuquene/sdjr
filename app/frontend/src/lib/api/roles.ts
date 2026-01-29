/**
 * Roles API Module
 * Handles role management (perfiles) endpoints
 */

import { ApiResponse, RoleFromAPI } from "@/types/admin";
import { fetchWithErrorHandling } from "./client";

// ============================================
// API Functions
// ============================================

/**
 * GET /api/v1/roles
 * Obtiene listado paginado de roles con permisos y conteo de usuarios
 */
export async function getRoles(perPage: number = 15): Promise<ApiResponse<RoleFromAPI>> {
  return fetchWithErrorHandling<ApiResponse<RoleFromAPI>>(
    `/api/v1/roles?per_page=${perPage}`
  );
}

/**
 * POST /api/v1/roles
 * Crea un nuevo rol
 * TODO: Implementar cuando se necesite
 */
export async function createRole(data: {
  name: string;
  description: string;
  permissions?: string[];
}): Promise<RoleFromAPI> {
  return fetchWithErrorHandling<RoleFromAPI>("/api/v1/roles", {
    method: "POST",
    body: JSON.stringify(data),
  });
}

/**
 * PATCH /api/v1/roles/{id}
 * Actualiza el estado de un rol (activo/inactivo)
 */
export async function updateRoleStatus(
  id: number,
  status: "0" | "1"
): Promise<RoleFromAPI> {
  return fetchWithErrorHandling<RoleFromAPI>(`/api/v1/roles/${id}`, {
    method: "PATCH",
    body: JSON.stringify({ status }),
  });
}

/**
 * PUT /api/v1/roles/{id}
 * Actualiza un rol existente
 * TODO: Implementar cuando se necesite
 */
export async function updateRole(
  id: number,
  data: Partial<{ name: string; description: string; permissions: string[] }>
): Promise<RoleFromAPI> {
  return fetchWithErrorHandling<RoleFromAPI>(`/api/v1/roles/${id}`, {
    method: "PUT",
    body: JSON.stringify(data),
  });
}

/**
 * DELETE /api/v1/roles/{id}
 * Elimina un rol
 * TODO: Implementar cuando se necesite
 */
export async function deleteRole(id: number): Promise<void> {
  return fetchWithErrorHandling<void>(`/api/v1/roles/${id}`, {
    method: "DELETE",
  });
}
