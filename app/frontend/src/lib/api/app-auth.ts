/**
 * App Auth API Module
 * Dedicated auth client for customer/app flows (separated from provider register).
 */

import type { LoginResponse, SessionData } from "@/types/auth";
import { mapLaravelRoleToRole, getDashboardPath } from "@/lib/roles";
import { API_URL, ApiError } from "./client";
import { login } from "./auth";

type AppLoginPayload = {
  email: string;
  password: string;
};

type AppRegisterPayload = {
  name: string;
  last_name?: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export type AppLoginResult = {
  ok: true;
  redirectTo?: string;
  user?: SessionData;
  token?: string;
};

export type AppRegisterResult = {
  ok: true;
  redirectTo?: string;
  user?: SessionData;
  token?: string;
};

function ensureRequiredFields(fields: Array<string>, message: string): void {
  const hasEmptyField = fields.some((value) => !value || value.trim().length === 0);
  if (hasEmptyField) {
    throw new Error(message);
  }
}

function splitNameParts(fullName: string, explicitLastName?: string): { name: string; last_name: string } {
  const normalizedName = fullName.trim().replace(/\s+/g, " ");
  const normalizedLastName = (explicitLastName ?? "").trim().replace(/\s+/g, " ");

  if (normalizedLastName.length > 0) {
    return {
      name: normalizedName,
      last_name: normalizedLastName,
    };
  }

  const parts = normalizedName.split(" ");
  if (parts.length >= 2) {
    return {
      name: parts[0],
      last_name: parts.slice(1).join(" "),
    };
  }

  return {
    name: normalizedName,
    last_name: "Usuario",
  };
}

function mapResponseToSession(data: LoginResponse): { user: SessionData; redirectTo: string } {
  const apiUser = data.data;
  const userRole = apiUser.roles && apiUser.roles.length > 0 ? apiUser.roles[0] : "user";
  const role = mapLaravelRoleToRole(userRole);

  const sessionUser: SessionData = {
    userId: apiUser.id?.toString() || "unknown",
    email: apiUser.email || "",
    role,
    name: apiUser.name,
    last_name: apiUser.last_name,
    token: data.token,
  };

  const redirectTo = getDashboardPath(role);
  return { user: sessionUser, redirectTo };
}

async function parseAuthResponse(response: Response): Promise<LoginResponse> {
  if (!response.ok) {
    const errorData = await response.json().catch(() => null);
    const apiMessage =
      errorData && typeof errorData === "object" && "message" in errorData
        ? String((errorData as { message: unknown }).message || "")
        : "";

    if (response.status === 404 || response.status === 405) {
      throw new ApiError(
        apiMessage || "Endpoint de registro app aun no disponible en backend.",
        response.status,
        errorData
      );
    }

    if (response.status === 422) {
      throw new ApiError(apiMessage || "Datos de registro invalidos.", 422, errorData);
    }

    if (response.status === 409) {
      throw new ApiError(apiMessage || "El correo ya esta registrado.", 409, errorData);
    }

    if (response.status === 401) {
      throw new ApiError(apiMessage || "Credenciales invalidas.", 401, errorData);
    }

    throw new ApiError(apiMessage || "Error del servidor.", response.status, errorData);
  }

  const payload: LoginResponse = await response.json();
  if (!payload?.data || !payload?.token) {
    throw new ApiError("Formato de respuesta inesperado.", 500, payload);
  }

  return payload;
}

/**
 * Dedicated login for app/customer surface.
 * Uses shared login endpoint but remains separate from provider auth module.
 */
export async function loginAppUser({ email, password }: AppLoginPayload): Promise<AppLoginResult> {
  return login({ email, password, scope: "customer" });
}

/**
 * Dedicated register for app/customer surface.
 * Backend endpoint: POST /api/v1/customer/register.
 */
export async function registerAppUser({
  name,
  last_name,
  email,
  password,
  password_confirmation,
}: AppRegisterPayload): Promise<AppRegisterResult> {
  ensureRequiredFields(
    [name, email, password, password_confirmation],
    "Por favor completa todos los campos."
  );

  const normalizedNameParts = splitNameParts(name, last_name);

  const response = await fetch(`${API_URL}/api/v1/customer/register`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({
      name: normalizedNameParts.name,
      last_name: normalizedNameParts.last_name,
      email,
      password,
      password_confirmation,
    }),
  });

  const payload = await parseAuthResponse(response);
  const { user, redirectTo } = mapResponseToSession(payload);

  return {
    ok: true,
    redirectTo,
    user,
    token: payload.token,
  };
}
