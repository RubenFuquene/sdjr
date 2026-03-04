/**
 * Authentication API Module
 * Handles login, logout, and authentication-related endpoints
 */

import type { LoginResponse, SessionData } from "@/types/auth";
import { mapLaravelRoleToRole, getDashboardPath } from "@/lib/roles";
import { API_URL } from "./client";

// ============================================
// Types
// ============================================

type LoginPayload = {
  email: string;
  password: string;
};

type RegisterPayload = {
  name: string;
  last_name: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export type LoginResult = {
  ok: true;
  redirectTo?: string;
  user?: SessionData;
  token?: string;
};

export type RegisterResult = {
  ok: true;
  redirectTo?: string;
  user?: SessionData;
  token?: string;
};

// ============================================
// API Functions
// ============================================

/**
 * POST /api/v1/login
 * Autentica usuario y retorna token + datos de sesión
 */
export async function login({ email, password }: LoginPayload): Promise<LoginResult> {
  if (!email || !password) {
    throw new Error("Ingresa correo y contraseña.");
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
      
      // Manejar errores específicos de la API
      if (response.status === 422) {
        throw new Error(errorData?.message || "Credenciales inválidas");
      }
      
      throw new Error(errorData?.message || "Error del servidor");
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
    
    throw new Error("Formato de respuesta inesperado");
  } catch (error) {
    // Re-lanzar errores conocidos
    if (error instanceof Error) {
      throw error;
    }
    
    // Error genérico de red u otros
    throw new Error("No se pudo conectar con el servidor");
  }
}

/**
 * POST /api/v1/provider/register
 * 
 * Endpoint público para registro de nuevos proveedores.
 * Auto-asigna rol "provider" y retorna token Sanctum para sesión inmediata.
 * 
 * Características:
 * - Público (sin requerir autenticación)
 * - Auto-asigna rol "provider"
 * - Retorna token Sanctum para sesión inmediata
 * - Responde con LoginResponse (mismo formato que /api/v1/login)
 * 
 * Validaciones esperadas (backend):
 * - name: requerido, max 255 caracteres
 * - last_name: requerido, max 255 caracteres
 * - email: requerido, único, válido
 * - password: requerido, min 8 caracteres
 * - password_confirmation: debe coincidir con password
 * 
 * Implementado en: app/Http/Controllers/Api/V1/ProviderRegisterController.php
 */
export async function register({ name, last_name, email, password, password_confirmation }: RegisterPayload): Promise<RegisterResult> {
  if (!name || !last_name || !email || !password || !password_confirmation) {
    throw new Error("Por favor completa todos los campos.");
  }

  try {
    const response = await fetch(`${API_URL}/api/v1/provider/register`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({ name, last_name, email, password, password_confirmation }),
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => null);
      
      // Manejar errores específicos de la API
      if (response.status === 422) {
        // Validación fallida (email duplicado, password débil, etc)
        throw new Error(errorData?.message || "El email ya está registrado");
      }
      
      throw new Error(errorData?.message || "Error del servidor");
    }

    const data: LoginResponse = await response.json();
    
    // Verificar que la respuesta tenga el formato esperado
    if (data?.message && data?.data) {
      const user = data.data;
      
      // Determinar rol del usuario (siempre "provider" para registro)
      const role = "provider";
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
    
    throw new Error("Formato de respuesta inesperado");
  } catch (error) {
    // Re-lanzar errores conocidos
    if (error instanceof Error) {
      throw error;
    }
    
    // Error genérico de red u otros
    throw new Error("No se pudo conectar con el servidor");
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
