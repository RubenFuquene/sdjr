/**
 * Administrators API Module
 * Handles administrator users endpoints
 */

import { fetchWithErrorHandling } from "./client";
import type { UserFromAPI } from "@/types/user";

// ============================================
// Response Types
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
// List - GET /api/v1/administrators
// ============================================

/**
 * GET /api/v1/administrators
 * Obtiene listado de usuarios administradores
 */
export async function getAdministrators(): Promise<ApiSuccess<UserFromAPI[]>> {
  return fetchWithErrorHandling<ApiSuccess<UserFromAPI[]>>(
    "/api/v1/administrators"
  );
}
