/**
 * Adaptadores y Mapeadores: Backend API → Frontend Types (Users)
 * Convierte tipos de Laravel (UserFromAPI) a tipos UI (Usuario)
 *
 * Responsabilidad única: Transformación de datos entre capas
 */

import type { Usuario } from './admin';
import type { UserFromAPI } from './user';

// ============================================
// Mapeadores Principales - Backend → Frontend
// ============================================

/**
 * Convierte UserFromAPI a Usuario
 * Usado para: Tabla de usuarios en dashboard
 *
 * Transformaciones:
 * - name → nombres
 * - last_name → apellidos
 * - phone → celular
 * - roles[0] → perfil (primer rol)
 * - status ('1'/'0') → activo (boolean)
 *
 * @param user - Respuesta del backend (UserResource)
 * @returns Usuario para frontend
 */
export const userFromAPIToUsuario = (user: UserFromAPI): Usuario => {
  return {
    id: user.id,
    nombres: user.name,
    apellidos: user.last_name,
    celular: user.phone,
    email: user.email,
    perfil: user.roles?.[0] || 'Sin rol',  // Primer rol o fallback
    activo: user.status === '1',            // '1' = true, '0' = false
  };
};

/**
 * Convierte array de UserFromAPI a Usuario[]
 * Usado para: Poblar tabla de usuarios
 *
 * @param users - Array de respuestas del backend
 * @returns Array de usuarios para frontend
 */
export const usersFromAPIToUsuarios = (users: UserFromAPI[]): Usuario[] => {
  return users.map(userFromAPIToUsuario);
};

// ============================================
// Mapeadores Inversos - Frontend → Backend
// ============================================

/**
 * Convierte Usuario a payload para update/create
 * Usado para: Enviar datos al backend
 *
 * Transformaciones inversas:
 * - nombres → name
 * - apellidos → last_name
 * - celular → phone
 * - activo (boolean) → status ('1'/'0')
 *
 * @param usuario - Usuario del frontend
 * @returns Payload para backend (PUT/POST)
 */
export const usuarioToBackendPayload = (
  usuario: Partial<Usuario>
): Record<string, unknown> => {
  const payload: Record<string, unknown> = {};

  if (usuario.nombres !== undefined) {
    payload.name = usuario.nombres;
  }
  if (usuario.apellidos !== undefined) {
    payload.last_name = usuario.apellidos;
  }
  if (usuario.celular !== undefined) {
    payload.phone = usuario.celular;
  }
  if (usuario.email !== undefined) {
    payload.email = usuario.email;
  }
  if (usuario.perfil !== undefined && usuario.perfil !== 'Sin rol') {
    payload.roles = [usuario.perfil];  // Backend espera array de roles
  }
  if (usuario.activo !== undefined) {
    payload.status = usuario.activo ? '1' : '0';
  }

  return payload;
};

// ============================================
// Validadores de Tipos
// ============================================

/**
 * Valida que UserFromAPI tenga los campos requeridos
 * Usado para: Type guard en runtime
 *
 * @param user - Objeto desconocido
 * @returns True si es UserFromAPI válido
 */
export const esUserFromAPIValido = (user: unknown): user is UserFromAPI => {
  if (!user || typeof user !== 'object') return false;

  const u = user as Record<string, unknown>;

  return (
    typeof u.id === 'number' &&
    typeof u.name === 'string' &&
    typeof u.last_name === 'string' &&
    typeof u.email === 'string' &&
    typeof u.phone === 'string' &&
    Array.isArray(u.roles) &&
    (u.status === '1' || u.status === '0') &&
    typeof u.created_at === 'string' &&
    typeof u.updated_at === 'string'
  );
};

/**
 * Valida array de UserFromAPI
 *
 * @param users - Array desconocido
 * @returns True si todos los elementos son UserFromAPI válidos
 */
export const esArrayUserFromAPIValido = (users: unknown): users is UserFromAPI[] => {
  if (!Array.isArray(users)) return false;
  return users.every(esUserFromAPIValido);
};

// ============================================
// Utilidades de Estado
// ============================================

/**
 * Convierte estado booleano a status del backend
 *
 * @param activo - Estado del frontend (true/false)
 * @returns Estado del backend ('A'/'I')
 */
export const activoToStatus = (activo: boolean): '1' | '0' => {
  return activo ? '1' : '0';
};

/**
 * Convierte status del backend a estado booleano
 *
 * @param status - Estado del backend ('1'/'0')
 * @returns Estado del frontend (true/false)
 */
export const statusToActivo = (status: '1' | '0'): boolean => {
  return status === '1';
};

// ============================================
// Utilidades de Roles
// ============================================

/**
 * Extrae el rol principal de un array de roles
 * Usado para: Mostrar un solo rol en la UI cuando hay múltiples
 *
 * @param roles - Array de roles del backend
 * @returns Rol principal o 'Sin rol'
 */
export const obtenerRolPrincipal = (roles: string[]): string => {
  if (!roles || roles.length === 0) return 'Sin rol';
  
  // Prioridad: admin > provider > user > otros
  const prioridad = ['admin', 'provider', 'user'];
  
  for (const rol of prioridad) {
    if (roles.includes(rol)) return rol;
  }
  
  // Si no hay rol con prioridad, devolver el primero
  return roles[0];
};

/**
 * Formatea roles para display en UI
 * Usado para: Mostrar roles de forma legible
 *
 * @param roles - Array de roles del backend
 * @returns String formateado (ej: "Admin, Provider")
 */
export const formatearRoles = (roles: string[]): string => {
  if (!roles || roles.length === 0) return 'Sin roles';
  
  return roles
    .map(rol => rol.charAt(0).toUpperCase() + rol.slice(1))
    .join(', ');
};
