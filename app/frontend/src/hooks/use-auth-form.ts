"use client";

import { useState } from "react";
import { login, register } from "@/lib/api/auth";
import { persistSession } from "@/lib/session";
import type { SessionData } from "@/types/auth";

/**
 * Custom hook para manejo de autenticación (login + registro)
 * Usado por LoginForm y RegisterForm
 *
 * Responsabilidades:
 * - Llamar a API (login/register)
 * - Persistir sesión en cookies
 * - Manejo de loading/error states
 * - Sanitización básica de datos
 */

interface UseAuthFormReturn {
  handleLogin: (email: string, password: string) => Promise<{ redirectTo: string; user: SessionData }>;
  handleRegister: (name: string, last_name: string, email: string, password: string, password_confirmation: string) => Promise<{ redirectTo: string; user: SessionData }>;
  loading: boolean;
  error: string | null;
  clearError: () => void;
}

export function useAuthForm(): UseAuthFormReturn {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  /**
   * Sanitización básica de entrada
   * NOTA: Backend también debe validar y sanitizar
   */
  const sanitizeEmail = (email: string): string => {
    return email.trim().toLowerCase();
  };

  const sanitizePassword = (password: string): string => {
    return password.trim();
  };

  const sanitizeName = (name: string): string => {
    return name.trim();
  };

  /**
   * Maneja login del usuario
   * Retorna { redirectTo, user } si es exitoso, throws Error si falla
   */
  const handleLogin = async (
    email: string,
    password: string
  ): Promise<{ redirectTo: string; user: SessionData }> => {
    setLoading(true);
    setError(null);

    try {
      // Sanitizar inputs
      const sanitizedEmail = sanitizeEmail(email);
      const sanitizedPassword = sanitizePassword(password);

      // Validación básica frontend
      if (!sanitizedEmail || !sanitizedPassword) {
        throw new Error("Por favor completa todos los campos");
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(sanitizedEmail)) {
        throw new Error("Por favor ingresa un correo válido");
      }

      if (sanitizedPassword.length < 6) {
        throw new Error("La contraseña debe tener al menos 6 caracteres");
      }

      // Llamar a API
      const result = await login({
        email: sanitizedEmail,
        password: sanitizedPassword,
      });

      if (!result.ok || !result.user) {
        throw new Error("Error al iniciar sesión");
      }

      // Persistir sesión
      persistSession(result.user);

      return {
        redirectTo: result.redirectTo || "/app/dashboard",
        user: result.user,
      };
    } catch (err) {
      // Extraer mensaje de error
      const message = err instanceof Error ? err.message : "Error desconocido";
      setError(message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  /**
   * Maneja registro de nuevo proveedor
   * Retorna { redirectTo, user } si es exitoso, throws Error si falla
   * 
   * Implementado en: POST /api/v1/provider/register
   */
  const handleRegister = async (
    name: string,
    last_name: string,
    email: string,
    password: string,
    password_confirmation: string
  ): Promise<{ redirectTo: string; user: SessionData }> => {
    setLoading(true);
    setError(null);

    try {
      // Sanitizar inputs
      const sanitizedName = sanitizeName(name);
      const sanitizedLastName = sanitizeName(last_name);
      const sanitizedEmail = sanitizeEmail(email);
      const sanitizedPassword = sanitizePassword(password);
      const sanitizedPasswordConfirmation = sanitizePassword(password_confirmation);

      // Validación básica frontend
      if (!sanitizedName || !sanitizedLastName || !sanitizedEmail || !sanitizedPassword || !sanitizedPasswordConfirmation) {
        throw new Error("Por favor completa todos los campos");
      }

      if (sanitizedName.length < 2) {
        throw new Error("El nombre debe tener al menos 2 caracteres");
      }

      if (sanitizedLastName.length < 2) {
        throw new Error("El apellido debe tener al menos 2 caracteres");
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(sanitizedEmail)) {
        throw new Error("Por favor ingresa un correo válido");
      }

      if (sanitizedPassword.length < 8) {
        throw new Error("La contraseña debe tener al menos 8 caracteres");
      }

      if (sanitizedPassword !== sanitizedPasswordConfirmation) {
        throw new Error("Las contraseñas no coinciden");
      }

      // Llamar a API
      const result = await register({
        name: sanitizedName,
        last_name: sanitizedLastName,
        email: sanitizedEmail,
        password: sanitizedPassword,
        password_confirmation: sanitizedPasswordConfirmation,
      });

      if (!result.ok || !result.user) {
        throw new Error("Error al crear cuenta");
      }

      // Persistir sesión
      persistSession(result.user);

      return {
        redirectTo: result.redirectTo || "/provider/dashboard",
        user: result.user,
      };
    } catch (err) {
      // Extraer mensaje de error
      const message = err instanceof Error ? err.message : "Error desconocido";
      setError(message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  /**
   * Limpia el mensaje de error
   */
  const clearError = (): void => {
    setError(null);
  };

  return {
    handleLogin,
    handleRegister,
    loading,
    error,
    clearError,
  };
}
