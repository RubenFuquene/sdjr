"use client";

import { useState } from "react";
import { loginAppUser, registerAppUser } from "@/lib/api/app-auth";
import { ApiError } from "@/lib/api/client";
import {
  getPasswordPolicyMessage,
  validatePasswordPolicy,
} from "@/lib/auth/password-policy";
import { persistSession } from "@/lib/session";
import type { SessionData } from "@/types/auth";

interface UseAppAuthFormReturn {
  loading: boolean;
  error: string | null;
  clearError: () => void;
  handleLogin: (
    email: string,
    password: string
  ) => Promise<{ redirectTo: string; user: SessionData }>;
  handleRegister: (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string
  ) => Promise<{ redirectTo: string; user: SessionData }>;
}

function sanitizeEmail(email: string): string {
  return email.trim().toLowerCase();
}

function sanitizePassword(password: string): string {
  return password.trim();
}

function sanitizeName(name: string): string {
  return name.trim();
}

type ApiValidationErrors = Record<string, string | string[]>;

function getValidationFieldError(data: unknown, fieldName: string): string | null {
  if (!data || typeof data !== "object") {
    return null;
  }

  const possibleErrors = (data as { errors?: unknown }).errors;
  if (!possibleErrors || typeof possibleErrors !== "object") {
    return null;
  }

  const fieldValue = (possibleErrors as ApiValidationErrors)[fieldName];
  if (Array.isArray(fieldValue) && fieldValue.length > 0) {
    return String(fieldValue[0]);
  }

  if (typeof fieldValue === "string" && fieldValue.trim().length > 0) {
    return fieldValue;
  }

  return null;
}

function mapApiError(error: unknown): string {
  if (error instanceof ApiError) {
    if (error.status === 0) {
      return "Error de conexion. Verifica tu internet e intenta de nuevo.";
    }

    if (error.status === 404 || error.status === 405) {
      return "Registro app aun no disponible. Intenta mas tarde.";
    }

    if (error.status === 401) {
      return "Credenciales invalidas. Verifica tu correo y contrasena.";
    }

    if (error.status === 409) {
      return "El correo ya esta registrado. Intenta iniciar sesion.";
    }

    if (error.status === 422) {
      const emailError = getValidationFieldError(error.data, "email");
      if (emailError) {
        return emailError;
      }

      const passwordError = getValidationFieldError(error.data, "password");
      if (passwordError) {
        return passwordError;
      }

      const passwordConfirmationError = getValidationFieldError(error.data, "password_confirmation");
      if (passwordConfirmationError) {
        return passwordConfirmationError;
      }

      const nameError = getValidationFieldError(error.data, "name");
      if (nameError) {
        return nameError;
      }

      return "Los datos ingresados no son validos.";
    }

    if (error.status >= 500) {
      return "Error del servidor. Intenta nuevamente en unos minutos.";
    }

    return error.message || "Error del servidor";
  }

  if (error instanceof Error) {
    return error.message || "Error del servidor";
  }

  return "Error desconocido";
}

export function useAppAuthForm(): UseAppAuthFormReturn {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const clearError = () => {
    setError(null);
  };

  const handleLogin = async (
    email: string,
    password: string
  ): Promise<{ redirectTo: string; user: SessionData }> => {
    setLoading(true);
    setError(null);

    try {
      const sanitizedEmail = sanitizeEmail(email);
      const sanitizedPassword = sanitizePassword(password);

      if (!sanitizedEmail || !sanitizedPassword) {
        throw new Error("Por favor completa todos los campos");
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(sanitizedEmail)) {
        throw new Error("Por favor ingresa un correo valido");
      }

      const result = await loginAppUser({
        email: sanitizedEmail,
        password: sanitizedPassword,
      });

      if (!result.ok || !result.user) {
        throw new Error("No se pudo iniciar sesion");
      }

      persistSession(result.user);

      return {
        redirectTo: result.redirectTo || "/app/dashboard",
        user: result.user,
      };
    } catch (apiError) {
      const mappedMessage = mapApiError(apiError);
      setError(mappedMessage);
      throw new Error(mappedMessage);
    } finally {
      setLoading(false);
    }
  };

  const handleRegister = async (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string
  ): Promise<{ redirectTo: string; user: SessionData }> => {
    setLoading(true);
    setError(null);

    try {
      const sanitizedName = sanitizeName(name);
      const sanitizedEmail = sanitizeEmail(email);
      const sanitizedPassword = sanitizePassword(password);
      const sanitizedConfirmation = sanitizePassword(passwordConfirmation);

      if (!sanitizedName || !sanitizedEmail || !sanitizedPassword || !sanitizedConfirmation) {
        throw new Error("Por favor completa todos los campos");
      }

      if (sanitizedName.length < 2) {
        throw new Error("El nombre debe tener al menos 2 caracteres");
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(sanitizedEmail)) {
        throw new Error("Por favor ingresa un correo valido");
      }

      const passwordPolicy = validatePasswordPolicy(sanitizedPassword);
      if (!passwordPolicy.isStrong) {
        throw new Error(getPasswordPolicyMessage(passwordPolicy));
      }

      if (sanitizedPassword !== sanitizedConfirmation) {
        throw new Error("Las contrasenas no coinciden");
      }

      const result = await registerAppUser({
        name: sanitizedName,
        email: sanitizedEmail,
        password: sanitizedPassword,
        password_confirmation: sanitizedConfirmation,
      });

      if (!result.ok || !result.user) {
        throw new Error("No se pudo crear la cuenta");
      }

      persistSession(result.user);

      return {
        redirectTo: result.redirectTo || "/app/dashboard",
        user: result.user,
      };
    } catch (apiError) {
      const mappedMessage = mapApiError(apiError);
      setError(mappedMessage);
      throw new Error(mappedMessage);
    } finally {
      setLoading(false);
    }
  };

  return {
    loading,
    error,
    clearError,
    handleLogin,
    handleRegister,
  };
}
