/**
 * Tipos para la gestión administrativa
 * Basado en diseños Figma para las 4 secciones: Perfiles, Proveedores, Usuarios, Administradores
 */

// ============================================
// API Response Types (Laravel Backend)
// ============================================

/**
 * Estructura genérica de respuesta paginada del backend Laravel
 */
export interface ApiResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
  };
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
}

/**
 * Rol tal como viene del endpoint /api/v1/roles
 */
export interface RoleFromAPI {
  id: number;
  name: string;
  description: string;
  permissions: Record<string, string>; // { "users.create": "Crear usuarios" }
  users_count: number;
}

/**
 * MAPEO DE DATOS: Backend API → Frontend Types
 * 
 * RoleFromAPI → Perfil:
 * - name → nombre
 * - description → descripcion
 * - permissions (filtrar por prefijo "admin.") → permisosAdmin: Array<{name, description}>
 * - permissions (filtrar por prefijo "provider.") → permisosProveedor: Array<{name, description}>
 * - users_count → usuarios
 * - Nota: Backend no envía campo "activo", se asume true por defecto
 */

// ============================================
// Frontend Types (UI Components)
// ============================================

export interface Perfil {
  id: number;
  nombre: string;
  descripcion: string;
  permisosAdmin: Array<{ name: string; description: string }>;
  permisosProveedor: Array<{ name: string; description: string }>;
  usuarios: number;
  activo: boolean;
}

export interface Proveedor {
  id: number;
  nombreComercial: string;
  nit: string;
  representanteLegal: string;
  tipoEstablecimiento: string;
  telefono: string;
  email: string;
  departamento: string;
  ciudad: string;
  perfil: string;
  activo: boolean;
}

export interface Usuario {
  id: number;
  nombres: string;
  apellidos: string;
  celular: string;
  email: string;
  perfil: string;
  activo: boolean;
}

export interface Administrador {
  id: number;
  nombres: string;
  apellidos: string;
  correo: string;
  area: string;
  perfil: string;
  activo: boolean;
}

export type Vista = 'perfiles' | 'proveedores' | 'usuarios' | 'administradores';
