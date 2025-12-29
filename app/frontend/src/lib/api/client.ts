/**
 * Core API Client - Base configuration and utilities
 * Shared across all API modules
 */

import { getAuthToken } from "@/lib/session";

export const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

// ============================================
// Error Handling
// ============================================

/**
 * Error personalizado para errores de API
 */
export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public data?: unknown
  ) {
    super(message);
    this.name = "ApiError";
  }
}

// ============================================
// Headers & Utilities
// ============================================

/**
 * Construye headers para peticiones autenticadas
 */
export function getAuthHeaders(): HeadersInit {
  const headers: HeadersInit = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };

  const token = getAuthToken();
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }
  
  return headers;
}

/**
 * Wrapper fetch con manejo de errores centralizado
 * Usado por todos los módulos de API
 */
export async function fetchWithErrorHandling<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const url = `${API_URL}${endpoint}`;

  try {
    const response = await fetch(url, {
      ...options,
      headers: {
        ...getAuthHeaders(),
        ...options.headers,
      },
    });

    // Manejo de errores HTTP
    if (!response.ok) {
      let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
      let errorData: unknown;

      try {
        errorData = await response.json();
        if (errorData && typeof errorData === "object" && "message" in errorData) {
          errorMessage = (errorData as { message: string }).message;
        }
      } catch {
        // Si no se puede parsear JSON, usar statusText
      }

      // Manejo específico de códigos de error
      switch (response.status) {
        case 401:
          throw new ApiError("No autorizado. Por favor inicia sesión.", 401, errorData);
        case 403:
          throw new ApiError("No tienes permisos para realizar esta acción.", 403, errorData);
        case 404:
          throw new ApiError("Recurso no encontrado.", 404, errorData);
        case 500:
          throw new ApiError("Error interno del servidor. Intenta de nuevo.", 500, errorData);
        default:
          throw new ApiError(errorMessage, response.status, errorData);
      }
    }

    return await response.json();
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }

    // Error de red o timeout
    throw new ApiError(
      "Error de conexión. Verifica tu conexión a internet.",
      0,
      error
    );
  }
}
