/**
 * Tipos para la gestión administrativa - Tipos genéricos
 * Para tipos específicos de Proveedores, ver: provider.ts
 *
 * Basado en diseños Figma para las 4 secciones: Perfiles, Proveedores, Usuarios, Administradores
 */

// Re-exportar tipos de Provider (SRP: cada módulo en su archivo)
export type { 
  Proveedor, 
  ProveedorListItem, 
  CommerceFromAPI,
  DocumentoProveedor,
  Sucursal,
  InformacionBancaria,
  Legal,
  ProveedorPayload,
  ProveedorUpdateResponse,
  DescargaDocumento,
  BancoOption,
  TipoCuenta,
  TipoDocumentoIdentidad,
  EstadoProveedor,
} from './provider';

// Re-exportar adaptadores de Provider
export {
  commerceToProveedorListItem,
  commerceToProveedor,
  commercesToProveedorListItems,
  mapearDocumentos,
  mapearSucursales,
  mapearInformacionBancaria,
  mapearInformacionLegal,
  proveedorToBackendPayload,
  esCommerceValido,
  esProveedorValido,
  normalizarEmail,
  normalizarTelefono,
  obtenerLabelEstado,
} from './provider.adapters';

// Re-exportar constantes de Provider
export { TIPOS_CUENTA, TIPOS_DOCUMENTO_IDENTIDAD } from './provider';

// ============================================
// API Response Types (Generic)
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
  status: string;
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
// Frontend Types - Usuarios y Perfiles
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
