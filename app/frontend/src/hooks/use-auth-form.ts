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
  handleLogin: (email: string, password: string) => Promise<SessionData>;
  handleRegister: (name: string, email: string, password: string) => Promise<SessionData>;
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
   * Retorna SessionData si es exitoso, throws Error si falla
   */
  const handleLogin = async (email: string, password: string): Promise<SessionData> => {
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

      return result.user;
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
   * Retorna SessionData si es exitoso, throws Error si falla
   * 
   * 🚨 NOTA: Requiere que backend haya implementado POST /api/v1/providers/register
   * Si aún no está implementado, fallará con mensaje claro.
   */
  const handleRegister = async (
    name: string,
    email: string,
    password: string
  ): Promise<SessionData> => {
    setLoading(true);
    setError(null);

    try {
      // Sanitizar inputs
      const sanitizedName = sanitizeName(name);
      const sanitizedEmail = sanitizeEmail(email);
      const sanitizedPassword = sanitizePassword(password);

      // Validación básica frontend
      if (!sanitizedName || !sanitizedEmail || !sanitizedPassword) {
        throw new Error("Por favor completa todos los campos");
      }

      if (sanitizedName.length < 2) {
        throw new Error("El nombre debe tener al menos 2 caracteres");
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(sanitizedEmail)) {
        throw new Error("Por favor ingresa un correo válido");
      }

      if (sanitizedPassword.length < 6) {
        throw new Error("La contraseña debe tener al menos 6 caracteres");
      }

      // Llamar a API
      const result = await register({
        name: sanitizedName,
        email: sanitizedEmail,
        password: sanitizedPassword,
      });

      if (!result.ok || !result.user) {
        throw new Error("Error al crear cuenta");
      }

      // Persistir sesión
      persistSession(result.user);

      return result.user;
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
