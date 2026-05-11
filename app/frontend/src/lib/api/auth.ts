/**
 * Authentication API Module
 * Handles login, logout, and authentication-related endpoints
 */

import type { LoginResponse, SessionData } from "@/types/auth";
import { mapLaravelRoleToRole, getDashboardPath } from "@/lib/roles";
import { API_URL, ApiError } from "./client";

// ============================================
// Types
// ============================================

type LoginPayload = {
  email: string;
  password: string;
};

type ForgotPasswordPayload = {
  email: string;
};

type ResetPasswordPayload = {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
};

export type LoginResult = {
  ok: true;
  redirectTo?: string;
  user?: SessionData;
  token?: string;
};

export type ForgotPasswordResult = {
  ok: true;
  message: string;
};

export type ResetPasswordResult = {
  ok: true;
  message: string;
};

type BasicApiResponse = {
  status?: boolean;
  message?: string;
};

function extractApiMessage(payload: unknown, fallback: string): string {
  if (payload && typeof payload === "object" && "message" in payload) {
    const value = (payload as { message?: unknown }).message;
    if (typeof value === "string" && value.trim().length > 0) {
      return value;
    }
  }

  return fallback;
}

async function readErrorBody(response: Response): Promise<unknown> {
  return await response.json().catch(() => null);
}

// ============================================
// API Functions
// ============================================

/**
 * POST /api/v1/login
 * Autentica usuario y retorna token + datos de sesión
 */
export async function login({ email, password }: LoginPayload): Promise<LoginResult> {
  if (!email || !password) {
    throw new ApiError("Ingresa correo y contraseña.", 422);
  }

  try {
    const response = await fetch(`${API_URL}/api/v1/login`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => null);
      const apiMessage =
        errorData && typeof errorData === "object" && "message" in errorData
          ? String((errorData as { message: unknown }).message || "")
          : "";
      
      // Manejar errores específicos de la API
      if (response.status === 422) {
        throw new ApiError(apiMessage || "Credenciales inválidas", 422, errorData);
      }

      if (response.status === 401) {
        throw new ApiError(apiMessage || "Credenciales inválidas", 401, errorData);
      }
      
      throw new ApiError(apiMessage || "Error del servidor", response.status, errorData);
    }

    const data: LoginResponse = await response.json();
    
    // Verificar que la respuesta tenga el formato esperado
    if (data?.message === "Login successful" && data?.data) {
      const user = data.data;
      
      // Determinar rol del usuario
      const userRole = user.roles && user.roles.length > 0 ? user.roles[0] : "admin";
      const role = mapLaravelRoleToRole(userRole);
      const redirectTo = getDashboardPath(role);
      
      // Construir SessionData tipada
      const sessionData: SessionData = {
        userId: user.id?.toString() || "unknown",
        email: user.email || "",
        role: role,
        name: user.name,
        last_name: user.last_name,
        token: data.token
      };
      
      return { 
        ok: true, 
        redirectTo,
        user: sessionData,
        token: data.token
      };
    }
    
    throw new ApiError("Formato de respuesta inesperado", 500, data);
  } catch (error) {
    // Re-lanzar errores conocidos
    if (error instanceof ApiError) {
      throw error;
    }

    if (error instanceof Error) {
      throw new ApiError(error.message || "Error del servidor", 0, error);
    }
    
    // Error genérico de red u otros
    throw new ApiError("No se pudo conectar con el servidor", 0, error);
  }
}

/**
 * POST /api/v1/password/forgot
 * Solicita correo de recuperación para una cuenta existente.
 */
export async function requestPasswordReset({ email }: ForgotPasswordPayload): Promise<ForgotPasswordResult> {
  const normalizedEmail = email.trim().toLowerCase();

  if (!normalizedEmail) {
    throw new ApiError("Ingresa un correo electrónico.", 422);
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizedEmail)) {
    throw new ApiError("Ingresa un correo válido.", 422);
  }

  try {
    const response = await fetch(`${API_URL}/api/v1/password/forgot`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ email: normalizedEmail }),
    });

    if (!response.ok) {
      const errorData = await readErrorBody(response);
      const apiMessage = extractApiMessage(errorData, "No se pudo enviar el correo de recuperación.");
      throw new ApiError(apiMessage, response.status, errorData);
    }

    const data = (await response.json().catch(() => null)) as BasicApiResponse | null;
    const message = extractApiMessage(data, "Recovery email sent successfully.");

    return {
      ok: true,
      message,
    };
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }

    if (error instanceof Error) {
      throw new ApiError(error.message || "Error del servidor", 0, error);
    }

    throw new ApiError("No se pudo conectar con el servidor", 0, error);
  }
}

/**
 * POST /api/v1/password/reset
 * Restablece contraseña usando token enviado al correo.
 */
export async function resetPassword({
  email,
  token,
  password,
  password_confirmation,
}: ResetPasswordPayload): Promise<ResetPasswordResult> {
  const normalizedEmail = email.trim().toLowerCase();

  if (!normalizedEmail || !token.trim() || !password || !password_confirmation) {
    throw new ApiError("Completa todos los campos.", 422);
  }

  if (password !== password_confirmation) {
    throw new ApiError("Las contraseñas no coinciden.", 422);
  }

  try {
    const response = await fetch(`${API_URL}/api/v1/password/reset`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        email: normalizedEmail,
        token: token.trim(),
        password,
        password_confirmation,
      }),
    });

    if (!response.ok) {
      const errorData = await readErrorBody(response);
      const apiMessage = extractApiMessage(errorData, "No se pudo restablecer la contraseña.");
      throw new ApiError(apiMessage, response.status, errorData);
    }

    const data = (await response.json().catch(() => null)) as BasicApiResponse | null;
    const message = extractApiMessage(data, "Password reset successfully.");

    return {
      ok: true,
      message,
    };
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }

    if (error instanceof Error) {
      throw new ApiError(error.message || "Error del servidor", 0, error);
    }

    throw new ApiError("No se pudo conectar con el servidor", 0, error);
  }
}

/**
 * POST /api/v1/logout
 * Cierra sesión del usuario
 * TODO: Implementar cuando el endpoint esté disponible en backend
 */
export async function logout(): Promise<void> {
  // TODO: Implementar llamada al backend
  // await fetchWithErrorHandling("/api/v1/logout", { method: "POST" });
  throw new Error("Logout endpoint not implemented yet");
}
