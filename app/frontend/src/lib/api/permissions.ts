/**
 * Permissions API Module
 * Handles permissions endpoints
 */

import { PermissionFromAPI } from "@/types/role-form-types";
import { fetchWithErrorHandling } from "./client";

// ============================================
// API Functions
// ============================================

/**
 * GET /api/v1/permissions
 * Obtiene listado de permisos desde el backend
 */
export async function getPermissions(): Promise<PermissionFromAPI[]> {
  const response = await fetchWithErrorHandling<{ data: PermissionFromAPI[] }>(
    `/api/v1/permissions`
  );
  return response.data || [];
}
