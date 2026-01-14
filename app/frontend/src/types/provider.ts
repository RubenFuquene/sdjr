/**
 * Tipos para la gestión de Proveedores (Commerces)
 * Provider Visualization Modal y gestión en panel administrativo
 *
 * Basado en:
 * - Backend: CommerceResource (Laravel API)
 * - Design: PROVIDER_VISUALIZATION_MODAL_ANALYSIS.md
 */

// ============================================
// API Response Types (Backend Laravel)
// ============================================

/**
 * Estructura del endpoint /api/v1/commerces (GET list)
 * Respuesta del backend Laravel - CommerceResource
 */
export interface CommerceFromAPI {
  id: number;
  owner_user: {
    id: number;
    name: string;
    email: string;
  };
  department: {
    id: number;
    name: string;
  };
  city: {
    id: number;
    name: string;
  };
  neighborhood?: {
    id: number;
    name: string;
  };
  legal_representatives: Array<{
    id: number;
    name: string;
    email: string;
  }>;
  name: string;
  description: string;
  tax_id: string;
  tax_id_type: string;
  address: string;
  phone: string;
  email: string;
  is_active: boolean;
  is_verified: boolean;
  created_at: string;
  updated_at: string;
}

// ============================================
// Frontend Types (UI Components)
// ============================================

/**
 * Tipo UI para mostrar proveedor en lista/tabla
 * Usado en: Dashboard Proveedores table
 */
export interface ProveedorListItem {
  id: number;
  nombreComercial: string;
  representanteLegal: string;
  telefono: string;
  email: string;
  perfil: string;
  estado: boolean; // is_active
  verificado: boolean; // is_verified
}

/**
 * Documento del proveedor
 * Almacena referencias a archivos
 */
export interface DocumentoProveedor {
  id?: string;
  tipo: 'cedula_ciudadania' | 'cedula_extranjeria' | 'pasaporte' | 'camara_comercio';
  nombre: string;
  url: string; // URL para descarga
  fechaSubida?: string;
}

/**
 * Sucursal/Sede del proveedor
 */
export interface Sucursal {
  id?: string;
  nombreSede: string;
  departamento: string;
  ciudad: string;
  barrio: string;
  direccion: string;
  nombreContacto: string;
  numeroContacto: string;
  horario: string;
  descripcion: string;
  fotos: string[]; // URLs de imágenes
}

/**
 * Información bancaria del proveedor
 */
export interface InformacionBancaria {
  titular: string;
  tipoCuenta: 'ahorros' | 'corriente' | 'fiduciaria';
  banco: string;
  numeroCuenta: string;
}

/**
 * Información legal del proveedor
 */
export interface Legal {
  aceptoTerminos: boolean;
  fechaAceptacion: string; // ISO date
}

/**
 * Estructura completa del proveedor para el modal de visualización/edición
 * Combina todos los datos: básicos, sucursales, bancaria, legal, documentos
 *
 * Usado en: ProviderVisualizationModal component
 */
export interface Proveedor {
  // Datos básicos
  id: number;
  nombreComercial: string;
  nit: string;
  representanteLegal: string;
  tipoEstablecimiento: string;
  telefono: string;
  email: string;
  departamento: string;
  ciudad: string;
  barrio: string;
  direccion: string;
  perfil: string; // ID del perfil/rol asignado
  estado: boolean; // is_active

  // Datos secundarios
  verificado: boolean; // is_verified
  descripcion?: string;

  // Relaciones
  documentos: DocumentoProveedor[];
  sucursales: Sucursal[];
  informacionBancaria?: InformacionBancaria;
  legal?: Legal;

  // Metadata
  fechaCreacion?: string;
  fechaActualizacion?: string;
}

/**
 * Payload para crear/actualizar proveedor
 * Estructura esperada por el backend en POST/PUT
 */
export interface ProveedorPayload {
  name: string;
  description?: string;
  tax_id: string;
  tax_id_type: 'NIT' | 'CE' | 'PASAPORTE';
  address: string;
  phone: string;
  email: string;
  department_id: number;
  city_id: number;
  neighborhood_id?: number;
  owner_user_id?: number;
}

/**
 * Respuesta de actualización de proveedor
 */
export interface ProveedorUpdateResponse {
  success: boolean;
  message: string;
  data?: Proveedor;
}

// ============================================
// Tipos de Descarga de Documentos
// ============================================

/**
 * Información para descargar documento
 */
export interface DescargaDocumento {
  documentoId: string;
  nombre: string;
  tipo: DocumentoProveedor['tipo'];
  url: string;
}

// ============================================
// Mapeadores y Helpers (Tipos Auxiliares)
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
 * Estados/estados de un proveedor
 */
export interface EstadoProveedor {
  activo: boolean;
  verificado: boolean;
  label: string; // "Activo y Verificado", "Inactivo", etc.
}
