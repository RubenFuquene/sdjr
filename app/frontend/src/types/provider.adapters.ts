/**
 * Adaptadores y Mapeadores: Backend API → Frontend Types
 * Convierte tipos de Laravel (CommerceFromAPI) a tipos UI (Proveedor, ProveedorListItem)
 *
 * Responsabilidad única: Transformación de datos entre capas
 */

import type {
  CommerceFromAPI,
  Proveedor,
  ProveedorListItem,
  DocumentoProveedor,
  Sucursal,
  InformacionBancaria,
  Legal,
} from './provider';

// ============================================
// Mapeadores Principales
// ============================================

/**
 * Convierte CommerceFromAPI a ProveedorListItem
 * Usado para: Dashboard tabla de proveedores
 *
 * @param commerce - Respuesta del backend
 * @returns Item para mostrar en lista
 */
export const commerceToProveedorListItem = (commerce: CommerceFromAPI): ProveedorListItem => {
  const representanteLegal = commerce.legal_representatives?.[0]?.name || 'N/A';

  return {
    id: commerce.id,
    nombreComercial: commerce.name,
    representanteLegal,
    telefono: commerce.phone || 'N/A',
    email: commerce.email,
    perfil: 'Proveedor', // TODO: Obtener del backend si está disponible
    estado: commerce.is_active,
    verificado: commerce.is_verified,
  };
};

/**
 * Convierte CommerceFromAPI a Proveedor (completo para modal)
 * Usado para: Modal de visualización/edición
 *
 * @param commerce - Respuesta del backend
 * @returns Estructura completa para el modal
 */
export const commerceToProveedor = (commerce: CommerceFromAPI): Proveedor => {
  const representanteLegal = commerce.legal_representatives?.[0]?.name || '';
  const barrio = commerce.neighborhood?.name || '';

  return {
    // Datos básicos
    id: commerce.id,
    nombreComercial: commerce.name,
    nit: commerce.tax_id,
    representanteLegal,
    tipoEstablecimiento: 'Comercial', // TODO: Obtener del backend si está disponible
    telefono: commerce.phone,
    email: commerce.email,
    departamento: commerce.department?.name || '',
    ciudad: commerce.city?.name || '',
    barrio,
    direccion: commerce.address,
    perfil: 'Proveedor', // TODO: Obtener del backend si está disponible
    estado: commerce.is_active,

    // Datos secundarios
    verificado: commerce.is_verified,
    descripcion: commerce.description,

    // Relaciones (vacías por ahora - requieren endpoints adicionales)
    documentos: [],
    sucursales: [],
    informacionBancaria: undefined,
    legal: undefined,

    // Metadata
    fechaCreacion: commerce.created_at,
    fechaActualizacion: commerce.updated_at,
  };
};

/**
 * Convierte array de CommerceFromAPI a ProveedorListItem[]
 * Usado para: Llenar tabla de dashboard
 *
 * @param commerces - Array de respuestas del backend
 * @returns Array de items para lista
 */
export const commercesToProveedorListItems = (commerces: CommerceFromAPI[]): ProveedorListItem[] => {
  return commerces.map(commerceToProveedorListItem);
};

// ============================================
// Mapeadores Específicos de Campos
// ============================================

/**
 * Mapea documentos desde estructura del backend a tipos UI
 * Estructura backend esperada: Campo "documentos" o similar
 * (Actualizar según estructura real del backend)
 *
 * @param backendDocumentos - Documentos del backend
 * @returns Array de DocumentoProveedor
 */
export const mapearDocumentos = (
  backendDocumentos?: unknown[]
): DocumentoProveedor[] => {
  if (!backendDocumentos || !Array.isArray(backendDocumentos)) {
    return [];
  }

  return backendDocumentos.map((doc) => {
    const docData = doc as Record<string, unknown>;
    const tipo = (docData.type as string) || 'cedula_ciudadania';
    
    // Validar que el tipo sea uno de los permitidos
    const tiposValidos: Array<DocumentoProveedor['tipo']> = [
      'cedula_ciudadania', 
      'cedula_extranjeria', 
      'pasaporte', 
      'camara_comercio'
    ];
    const tipoValidado = tiposValidos.includes(tipo as DocumentoProveedor['tipo']) 
      ? (tipo as DocumentoProveedor['tipo']) 
      : 'cedula_ciudadania';
    
    return {
      id: docData.id?.toString() ?? '',
      tipo: tipoValidado,
      nombre: (docData.name as string) || 'Documento',
      url: (docData.url as string) || '#',
      fechaSubida: docData.created_at as string | undefined,
    };
  });
};

/**
 * Mapea sucursales desde estructura del backend a tipos UI
 * Estructura backend esperada: Campo "branches" o "locations"
 * (Actualizar según estructura real del backend)
 *
 * @param backendSucursales - Sucursales del backend
 * @returns Array de Sucursal
 */
export const mapearSucursales = (
  backendSucursales?: unknown[]
): Sucursal[] => {
  if (!backendSucursales || !Array.isArray(backendSucursales)) {
    return [];
  }

  return backendSucursales.map((sucursal) => {
    const sucursalData = sucursal as Record<string, unknown>;
    const department = sucursalData.department as Record<string, unknown> | undefined;
    const city = sucursalData.city as Record<string, unknown> | undefined;
    const neighborhood = sucursalData.neighborhood as Record<string, unknown> | undefined;
    
    return {
      id: sucursalData.id?.toString() ?? '',
      nombreSede: (sucursalData.name as string) || 'Sede',
      departamento: (department?.name as string) || '',
      ciudad: (city?.name as string) || '',
      barrio: (neighborhood?.name as string) || '',
      direccion: (sucursalData.address as string) || '',
      nombreContacto: (sucursalData.contact_name as string) || '',
      numeroContacto: (sucursalData.contact_phone as string) || '',
      horario: (sucursalData.hours as string) || '',
      descripcion: (sucursalData.description as string) || '',
      fotos: (sucursalData.photos as string[]) || [],
    };
  });
};

/**
 * Mapea información bancaria desde estructura del backend a tipos UI
 * Estructura backend esperada: Campo "bank_account" o similar
 * (Actualizar según estructura real del backend)
 *
 * @param backendBanking - Info bancaria del backend
 * @returns InformacionBancaria o undefined
 */
export const mapearInformacionBancaria = (
  backendBanking?: unknown
): InformacionBancaria | undefined => {
  if (!backendBanking || typeof backendBanking !== 'object') {
    return undefined;
  }

  const bankingData = backendBanking as Record<string, unknown>;
  
  return {
    titular: (bankingData.holder_name as string) || '',
    tipoCuenta: mapearTipoCuenta(bankingData.account_type as string | undefined),
    banco: (bankingData.bank_name as string) || '',
    numeroCuenta: (bankingData.account_number as string) || '',
  };
};

/**
 * Mapea tipo de cuenta de backend a tipo UI
 * Convierte valores del backend a valores conocidos del frontend
 */
const mapearTipoCuenta = (backendType?: string): 'ahorros' | 'corriente' | 'fiduciaria' => {
  if (!backendType) return 'corriente';

  const typeMap: Record<string, 'ahorros' | 'corriente' | 'fiduciaria'> = {
    'savings': 'ahorros',
    'ahorros': 'ahorros',
    'checking': 'corriente',
    'corriente': 'corriente',
    'fiduciary': 'fiduciaria',
    'fiduciaria': 'fiduciaria',
  };

  return typeMap[backendType.toLowerCase()] || 'corriente';
};

/**
 * Mapea información legal desde estructura del backend a tipos UI
 * Estructura backend esperada: Campo "legal" o flags "accepted_terms"
 * (Actualizar según estructura real del backend)
 *
 * @param backendLegal - Info legal del backend
 * @returns Legal o undefined
 */
export const mapearInformacionLegal = (
  backendLegal?: unknown
): Legal | undefined => {
  if (!backendLegal || typeof backendLegal !== 'object') {
    return undefined;
  }

  const legalData = backendLegal as Record<string, unknown>;
  
  if (!legalData.accepted_terms) {
    return undefined;
  }

  return {
    aceptoTerminos: Boolean(legalData.accepted_terms),
    fechaAceptacion: (legalData.acceptance_date as string) || new Date().toISOString(),
  };
};

// ============================================
// Mapeador Inverso: Frontend → Backend
// (Para enviar cambios al servidor)
// ============================================

/**
 * Convierte Proveedor (tipo UI) a payload para enviar al backend
 * Usado para: PUT /api/v1/commerces/:id
 *
 * @param proveedor - Datos del proveedor editados en frontend
 * @returns Payload para enviar al backend
 */
export const proveedorToBackendPayload = (proveedor: Proveedor) => {
  return {
    name: proveedor.nombreComercial,
    description: proveedor.descripcion || '',
    tax_id: proveedor.nit,
    tax_id_type: 'NIT', // TODO: Hacer dinámico
    address: proveedor.direccion,
    phone: proveedor.telefono,
    email: proveedor.email,
    // Los IDs de departamento/ciudad requieren búsqueda previa
    // department_id: departmentId,
    // city_id: cityId,
    // neighborhood_id: neighborhoodId,
  };
};

// ============================================
// Validadores de Mapeo
// ============================================

/**
 * Valida que CommerceFromAPI tenga los campos requeridos
 * Útil para debugging y validación de respuestas del backend
 *
 * @param commerce - Objeto a validar
 * @returns true si es válido
 */
export const esCommerceValido = (commerce: unknown): commerce is CommerceFromAPI => {
  if (!commerce || typeof commerce !== 'object') {
    return false;
  }
  
  const commerceData = commerce as Record<string, unknown>;
  
  return (
    typeof commerceData.id === 'number' &&
    typeof commerceData.name === 'string' &&
    typeof commerceData.email === 'string' &&
    typeof commerceData.phone === 'string'
  );
};

/**
 * Valida que Proveedor tenga datos mínimos requeridos
 * Útil antes de mostrar en modal o enviar al servidor
 *
 * @param proveedor - Objeto a validar
 * @returns true si es válido
 */
export const esProveedorValido = (proveedor: unknown): proveedor is Proveedor => {
  if (!proveedor || typeof proveedor !== 'object') {
    return false;
  }
  
  const proveedorData = proveedor as Record<string, unknown>;
  
  return (
    typeof proveedorData.id === 'number' &&
    typeof proveedorData.nombreComercial === 'string' &&
    typeof proveedorData.email === 'string' &&
    proveedorData.nombreComercial.toString().trim().length > 0
  );
};

// ============================================
// Utilities para Transformaciones
// ============================================

/**
 * Normaliza un email (trim, lowercase)
 */
export const normalizarEmail = (email: string): string => {
  return email.trim().toLowerCase();
};

/**
 * Normaliza un teléfono (remove espacios, guiones)
 */
export const normalizarTelefono = (telefono: string): string => {
  return telefono.replace(/[\s-]/g, '');
};

/**
 * Obtiene label de estado del proveedor
 */
export const obtenerLabelEstado = (activo: boolean, verificado: boolean): string => {
  if (!activo) return 'Inactivo';
  if (!verificado) return 'Pendiente verificación';
  return 'Activo y Verificado';
};
