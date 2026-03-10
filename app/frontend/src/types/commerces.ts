/**
 * Tipos para la gestión de Comercios (Commerces)
 * Entidad de negocio: Tiendas/Proveedores registrados en el sistema
 *
 * Basado en:
 * - Backend: CommerceResource (Laravel API)
 * - Design: PROVIDER_VISUALIZATION_MODAL_ANALYSIS.md
 */

// ============================================
// API Response Types (Backend Laravel)
// ============================================

/**
 * Estado de verificacion del comercio en backend:
 * 0 = Pendiente, 1 = Activo, 2 = Rechazado
 */
export type CommerceVerificationStatus = 0 | 1 | 2;

/**
 * Estructura del endpoint /api/v1/commerces (GET list)
 * Respuesta del backend Laravel - CommerceResource
 */
export interface CommerceFromAPI {
  id: number;
  owner_user: {
    id: number;
    name: string;
    last_name?: string;
    email: string;
    phone?: string;
    roles?: string[];
    status?: string;
    created_at?: string | null;
    updated_at?: string | null;
  };
  department: {
    id: number;
    name: string;
    country_id?: number;
    code?: string;
    status?: string;
    created_at?: string;
    updated_at?: string;
  };
  city: {
    id: number;
    name: string;
    department_id?: number;
    code?: string;
    status?: string;
    created_at?: string;
    updated_at?: string;
  };
  neighborhood?: {
    id: number;
    name: string;
    city_id?: number;
    code?: string;
    status?: string;
    created_at?: string;
    updated_at?: string;
  };
  legal_representatives: Array<{
    id: number;
    commerce_id?: number;
    name: string;
    last_name: string;
    document: string;
    document_type: string;
    email?: string | null;
    phone?: string | null;
    is_primary?: boolean;
    status?: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
  }>;
  documents: CommerceDocumentFromAPI[];
  name: string;
  description?: string;
  tax_id: string;
  tax_id_type: string;
  address: string;
  phone?: string;
  email?: string;
  is_active: boolean;
  is_verified: CommerceVerificationStatus;
  created_at: string;
  updated_at: string;
}

// ============================================
// Frontend Types (UI Components)
// ============================================

/**
 * Tipo UI para mostrar commerce en lista/tabla
 * Usado en: Dashboard Comercios table
 */
export interface ProveedorListItem {
  id: number;
  nombreComercial: string;
  representanteLegal: string;
  telefono: string;
  email: string;
  perfil: string;
  estado: boolean; // is_active (operativo)
  estadoVerificacion: CommerceVerificationStatus; // is_verified (0=Pendiente, 1=Activo, 2=Rechazado)
  verificado: boolean; // Deprecated: usar estadoVerificacion
  tipoEstablecimiento?: string; // Tipo de establecimiento (Ej: Restaurante, Tienda, etc)
  createdAt?: string; // Fecha de creación/solicitud (ISO 8601)
}

/**
 * Documento del commerce
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
 * Sucursal/Sede del commerce
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
 * Información bancaria del commerce
 */
export interface InformacionBancaria {
  titular: string;
  tipoCuenta: 'ahorros' | 'corriente' | 'fiduciaria';
  banco: string;
  numeroCuenta: string;
}

/**
 * Información legal del commerce
 */
export interface Legal {
  aceptoTerminos: boolean;
  fechaAceptacion: string; // ISO date
}

/**
 * Estructura completa del commerce para el modal de visualización/edición
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

// ============================================
// API Payload Types
// ============================================

/**
 * Payload para crear/actualizar commerce
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
 * Payload para crear commerce con datos básicos
 * POST /api/v1/commerces/basic (Registro de Proveedor)
 * Corresponde a CommerceBasicDataRequest en backend Laravel
 */
export interface CommerceBasicPayload {
  commerce: {
    owner_user_id: number;
    department_id: number;
    city_id: number;
    neighborhood_id: number;
    name: string;
    description?: string;
    tax_id: string;
    tax_id_type: 'NIT' | 'CC' | 'PS' | 'CE';
    address: string;
    phone?: string;
    email?: string;
    is_verified?: CommerceVerificationStatus;
    is_active?: boolean;
  };
  legal_representative?: {
    name: string;
    last_name: string;
    document: string;
    document_type: 'CC' | 'CE' | 'NIT' | 'PAS';
    email?: string;
    phone?: string;
    is_primary?: boolean;
  };
  commerce_documents?: Array<{
    verified_by_id?: number;
    uploaded_by_id?: number;
    document_type?: string;
    file_path?: string;
    mime_type?: string;
    verified?: boolean;
    uploaded_at?: string;
    verified_at?: string;
  }>;
  my_account?: {
    type: string;
    account_type: string;
    bank_id: number;
    account_number: string;
    owner_id: number;
    is_primary?: boolean;
  };
}

/**
 * Respuesta de actualización de commerce
 */
export interface ProveedorUpdateResponse {
  success: boolean;
  message: string;
  data?: Proveedor;
}

// ============================================
// API Response Types - Specific Endpoints
// ============================================

/**
 * Documento del comercio en respuesta backend
 * Estructura de CommerceDocumentResource
 */
export interface CommerceDocumentFromAPI {
  id: number;
  document_type: string;
  file_path: string;
  mime_type: string;
  file_size_bytes: number;
  upload_status: string; // 'confirmed', 'pending', etc
  version_number: number;
  verified: boolean;
  verified_by_id?: number;
  uploaded_by_id?: number;
  uploaded_at?: string | null;
  verified_at?: string | null;
  download?: {
    mode: string; // 'url'
    endpoint: string; // URL para descargar
  };
  created_at?: string;
  updated_at?: string;
}

/**
 * Método de pago del comercio en respuesta backend
 * Estructura de CommercePayoutMethodResource (my_account en respuesta básica)
 */
export interface CommercePayoutMethodFromAPI {
  id: number;
  commerce_id?: number;
  type: string;
  account_type: string;
  bank_id: number;
  account_number: string;
  owner_id?: number;
  is_primary?: boolean;
  created_at?: string;
  updated_at?: string;
}

/**
 * Respuesta específica del endpoint POST /api/v1/commerces/basic
 * Incluye: commerce + documentos + métodos de pago
 * Corresponde a CommerceBasicDataResource en backend
 */
export interface CommerceBasicDataResponse {
  commerce: CommerceFromAPI;
  commerce_documents: CommerceDocumentFromAPI[];
  my_account: CommercePayoutMethodFromAPI[];
}
