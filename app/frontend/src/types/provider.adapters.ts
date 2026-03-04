/**
 * Adaptadores y Mapeadores de Proveedores (como ROL/USUARIO)
 * Contiene transformaciones específicas del usuario con rol de proveedor
 * 
 * Para adaptadores de Comercios (entidad), ver: commerces.adapters.ts
 * Responsabilidad única: Transformación de datos de usuario/proveedor
 */

import type { DescargaDocumento, BancoOption } from './provider';
import type { ProveedorPayload } from './commerces';
import type { BasicInfoFormData, DocumentType } from './basic-info';

// ============================================
// Utilities para Proveedores
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

// ============================================
// Mapper: BasicInfoFormData -> ProveedorPayload
// ============================================

export const basicInfoToProveedorPayload = (
  formData: BasicInfoFormData,
  ownerUserId: number
): ProveedorPayload => {
  const departmentId = requireNumber(formData.departmentId, 'department_id');
  const cityId = requireNumber(formData.cityId, 'city_id');
  const neighborhoodId = parseNeighborhoodId(formData.neighborhood);

  return {
    name: formData.commercialName.trim(),
    description: formData.observations?.trim() || undefined,
    tax_id: formData.documentNumber.trim(),
    tax_id_type: mapDocumentTypeToTaxIdType(formData.documentType),
    address: formData.mainAddress.trim(),
    phone: formData.phone.trim(),
    email: formData.email.trim(),
    department_id: departmentId,
    city_id: cityId,
    neighborhood_id: neighborhoodId,
    owner_user_id: ownerUserId,
  };
};

/**
 * Mapea DocumentType de frontend a tax_id_type esperado
 * Valores esperados: 'NIT' | 'CE' | 'PASAPORTE'
 */
const mapDocumentTypeToTaxIdType = (
  documentType: DocumentType | ''
): ProveedorPayload['tax_id_type'] => {
  switch (documentType) {
    case 'nit':
      return 'NIT';
    case 'ce':
      return 'CE';
    case 'passport':
      return 'PASAPORTE';
    case 'cc':
    default:
      return 'NIT';
  }
};

// ============================================
// Helpers
// ============================================

const requireNumber = (value: number | null, field: string): number => {
  if (typeof value !== 'number') {
    throw new Error(`${field}_required`);
  }

  return value;
};

const parseNeighborhoodId = (value: string): number => {
  const parsed = Number.parseInt(value, 10);
  if (Number.isNaN(parsed)) {
    throw new Error('neighborhood_id_invalid');
  }

  return parsed;
};