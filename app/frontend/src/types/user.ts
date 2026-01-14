/**
 * Tipos para gestión de usuarios
 * Estructura de datos del backend (UserResource) y payloads
 */

// ============================================
// Backend Structure - UserResource
// ============================================

/**
 * Estructura del usuario según backend (UserResource)
 */
export interface UserFromAPI {
  id: number;
  name: string;              // Backend: name
  last_name: string;         // Backend: last_name
  email: string;             // Backend: email
  phone: string;             // Backend: phone
  roles: string[];           // Backend: roles array (spatie)
  status: string;            // Backend: boolean como entero ('1' activo, '0' inactivo)
  created_at: string;        // ISO timestamp
  updated_at: string;        // ISO timestamp
}

// ============================================
// Query Parameters
// ============================================

/**
 * Parámetros para GET /api/v1/users
 */
export interface GetUsersParams {
  page?: number;
  perPage?: number;
  search?: string;    // Backend: busca en name, last_name, email
  role?: string;      // Backend: filtra por nombre de rol
  status?: 'A' | 'I'; // Backend: 'A' activo, 'I' inactivo
}

// ============================================
// Request Payloads
// ============================================

/**
 * Payload para POST /api/v1/users (crear usuario)
 */
export interface CreateUserPayload {
  name: string;
  last_name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation?: string;
  status?: 'A' | 'I';
  roles?: string[];          // Array de nombres de roles
}

/**
 * Payload para PUT /api/v1/users/{id} (actualizar usuario)
 */
export interface UpdateUserPayload {
  name?: string;
  last_name?: string;
  email?: string;
  phone?: string;
  password?: string;
  password_confirmation?: string;
  status?: 'A' | 'I';
  roles?: string[];
}
