/**
 * Provider Auth API Module
 * Dedicated provider registration endpoint.
 */

import type { LoginResponse, SessionData } from "@/types/auth";
import { getDashboardPath } from "@/lib/roles";
import { API_URL } from "./client";

type ProviderRegisterPayload = {
  name: string;
  last_name?: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export type ProviderRegisterResult = {
  ok: true;
  redirectTo?: string;
  user?: SessionData;
  token?: string;
};

/**
 * POST /api/v1/provider/register
 * Public provider registration endpoint.
 */
export async function registerProvider({
  name,
  last_name,
  email,
  password,
  password_confirmation,
}: ProviderRegisterPayload): Promise<ProviderRegisterResult> {
  if (!name || !email || !password || !password_confirmation) {
    throw new Error("Por favor completa todos los campos.");
  }

  // MVP compatibility: current provider model expects last_name.
  const normalizedLastName = (last_name || "").trim() || "provider";

  try {
    const response = await fetch(`${API_URL}/api/v1/provider/register`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        name,
        last_name: normalizedLastName,
        email,
        password,
        password_confirmation,
      }),
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => null);

      if (response.status === 422) {
        throw new Error(errorData?.message || "El email ya esta registrado");
      }

      throw new Error(errorData?.message || "Error del servidor");
    }

    const data: LoginResponse = await response.json();

    if (data?.message && data?.data) {
      const user = data.data;
      const role = "provider";
      const redirectTo = getDashboardPath(role);

      const sessionData: SessionData = {
        userId: user.id?.toString() || "unknown",
        email: user.email || "",
        role,
        name: user.name,
        last_name: user.last_name,
        token: data.token,
      };

      return {
        ok: true,
        redirectTo,
        user: sessionData,
        token: data.token,
      };
    }

    throw new Error("Formato de respuesta inesperado");
  } catch (error) {
    if (error instanceof Error) {
      throw error;
    }

    throw new Error("No se pudo conectar con el servidor");
  }
}
