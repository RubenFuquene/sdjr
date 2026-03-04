/**
 * Tipos relacionados con Proveedores como ROL/USUARIO
 * Esta carpeta contiene tipos para la gestión de usuarios con rol de proveedor
 * 
 * Para tipos de Comercios (entidad de negocio), ver: commerces.ts
 * Para adaptadores de Comercios, ver: commerces.adapters.ts
 */

// ============================================
// Tipos de Descarga de Documentos
// ============================================

/**
 * Información para descargar documento
 */
export interface DescargaDocumento {
  documentoId: string;
  nombre: string;
  tipo: 'cedula_ciudadania' | 'cedula_extranjeria' | 'pasaporte' | 'camara_comercio';
  url: string;
}

// ============================================
// Mapeadores y Helpers (Tipos Auxiliares para Rol)
// ============================================

/**
 * Opciones para select de bancos
 */
export interface BancoOption {
  id: string;
  nombre: string;
  codigo?: string;
}

/**
 * Opciones para select de tipos de cuenta
 */
export const TIPOS_CUENTA = {
  ahorros: 'Cuenta de Ahorros',
  corriente: 'Cuenta Corriente',
  fiduciaria: 'Cuenta Fiduciaria',
} as const;

export type TipoCuenta = keyof typeof TIPOS_CUENTA;

/**
 * Opciones para select de tipos de documento de identidad
 */
export const TIPOS_DOCUMENTO_IDENTIDAD = {
  NIT: 'NIT',
  CE: 'Cédula de Extranjería',
  PASAPORTE: 'Pasaporte',
} as const;

export type TipoDocumentoIdentidad = keyof typeof TIPOS_DOCUMENTO_IDENTIDAD;

/**
 * Estados/estados de un usuario con rol de proveedor
 */
export interface EstadoProveedor {
  activo: boolean;
  verificado: boolean;
  label: string; // "Activo y Verificado", "Inactivo", etc.
}
