"use client";

import { toast } from "sonner";
import { ApiError } from "@/lib/api/client";

/**
 * Hook centralizado para manejo de errores de API
 * 
 * Captura errores HTTP y los muestra al usuario con Sonner
 * Re-lanza el error para que el componente pueda hacer lógica adicional
 * 
 * @example
 * const handleError = useApiErrorHandler();
 * 
 * try {
 *   await updateRole(...);
 * } catch (error) {
 *   handleError(error); // Muestra toast y re-lanza
 * }
 */
export function useApiErrorHandler() {
  /**
   * Mapeo de status codes HTTP a mensajes amigables
   */
  const getErrorMessage = (status: number, defaultMessage: string): string => {
    const messages: Record<number, string> = {
      401: "Sesión expirada. Por favor, inicia sesión de nuevo.",
      403: "No tienes permisos para realizar esta acción.",
      404: "Recurso no encontrado.",
      405: "Esta funcionalidad aún no está disponible. Por favor, contacta al equipo de soporte.",
      422: "Los datos proporcionados no son válidos.",
      500: "Error interno del servidor. Intenta de nuevo más tarde.",
    };

    return messages[status] || defaultMessage;
  };

  /**
   * Maneja un error capturado y muestra notificación al usuario
   * Re-lanza el error para que el caller pueda hacer lógica adicional
   */
  const handleError = (error: unknown): never => {
    console.log("Handling API error:", error);
    if (error instanceof ApiError) {
      const message = getErrorMessage(error.status, error.message);
      console.error(`[API Error ${error.status}]`, error.message, error.data);
      toast.error(message);
    } else if (error instanceof Error) {
      console.error("[Error]", error.message);
      toast.error(error.message || "Error desconocido. Por favor, intenta de nuevo.");
    } else {
      console.error("[Unknown Error]", error);
      toast.error("Error desconocido. Por favor, intenta de nuevo.");
    }

    // Re-lanzar el error para que el componente pueda hacer lógica adicional
    // (p.ej. revertir cambios optimistas, resetear formularios, etc.)
    throw error;
  };

  return handleError;
}
