/**
 * Session Management Constants and Utilities
 * Centraliza el manejo de sesión (cookies, tokens, SessionData)
 */

import type { SessionData } from "@/types/auth";

// ============================================
// Constants
// ============================================

/**
 * Nombre de la cookie que almacena la sesión del usuario
 * Usado en login-form, middleware, y auth guards
 */
export const SESSION_COOKIE_NAME = "sdjr_session";

/**
 * Duración de la sesión (7 días en segundos)
 */
export const SESSION_MAX_AGE = 60 * 60 * 24 * 7;

// ============================================
// Client-Side Utilities (Browser)
// ============================================

/**
 * Guarda la sesión en cookie (client-side)
 * Usado después del login exitoso
 */
export function persistSession(sessionData: SessionData): void {
  if (typeof window === "undefined") return;
  
  const encodedData = encodeURIComponent(JSON.stringify(sessionData));
  document.cookie = `${SESSION_COOKIE_NAME}=${encodedData}; path=/; max-age=${SESSION_MAX_AGE}; SameSite=Lax`;
}

/**
 * Lee la sesión desde cookie (client-side)
 * Retorna SessionData o null si no existe/es inválida
 */
export function getSessionFromCookie(): SessionData | null {
  if (typeof window === "undefined") return null;
  
  const cookies = document.cookie.split("; ");
  const sessionCookie = cookies.find(cookie => cookie.startsWith(`${SESSION_COOKIE_NAME}=`));
  
  if (!sessionCookie) return null;
  
  try {
    const value = sessionCookie.split("=")[1];
    const decoded = decodeURIComponent(value);
    return JSON.parse(decoded);
  } catch {
    return null;
  }
}

/**
 * Obtiene el token de autenticación desde la sesión
 * Usado para peticiones API autenticadas
 */
export function getAuthToken(): string | null {
  const session = getSessionFromCookie();
  return session?.token || null;
}

/**
 * Elimina la sesión (logout)
 */
export function clearSession(): void {
  if (typeof window === "undefined") return;
  
  document.cookie = `${SESSION_COOKIE_NAME}=; path=/; max-age=0; SameSite=Lax`;
}

// ============================================
// Server-Side Utilities (Next.js Server Components/Actions)
// ============================================

/**
 * Lee la sesión desde cookies en el servidor (Next.js)
 * Para usar con cookies() de next/headers
 */
export async function getSessionFromServerCookies(
  cookieStore: Awaited<ReturnType<typeof import("next/headers").cookies>>
): Promise<SessionData | null> {
  const sessionData = cookieStore.get(SESSION_COOKIE_NAME)?.value;
  
  if (!sessionData) return null;
  
  try {
    const parsed = JSON.parse(decodeURIComponent(sessionData));
    return {
      userId: parsed.userId || "unknown",
      email: parsed.email || "",
      role: parsed.role || "admin",
      name: parsed.name,
      last_name: parsed.last_name,
      token: parsed.token,
    };
  } catch {
    return null;
  }
}
