/**
 * Tipos para el formulario Datos Básicos
 * Estructura completa del formulario con todos los campos
 */

/**
 * Tipo de documento (documento de identidad)
 */
export type DocumentType = 'nit' | 'cc' | 'ce' | 'passport';

/**
 * Tipo de establecimiento
 */
export type EstablishmentType = 'restaurant' | 'cafeteria' | 'bakery' | 'fast_food' | 'desserts' | 'drinks' | 'other';

/**
 * Información del Representante Legal
 */
export interface LegalRepresentative {
  firstName: string;
  lastName: string;
  documentType: DocumentType | '';
  documentNumber: string;
  documentFile: string | null;
}

/**
 * Documentos adjuntos
 */
export interface DocumentsInfo {
  identity: string | null; // Cédula de ciudadanía, extranjería o pasaporte del representante
  commerceChamber: string | null; // Cámara de comercio del establecimiento
}

/**
 * Estructura completa del formulario Datos Básicos
 */
export interface BasicInfoFormData {
  // Información del Establecimiento
  commercialName: string;
  documentType: DocumentType | '';
  documentNumber: string;
  establishmentType: EstablishmentType | '';
  phone: string;
  email: string;
  departmentId: number | null;
  cityId: number | null;
  neighborhood: string; // Puede ser ID o nombre manual
  mainAddress: string;

  // Representante Legal
  legalRepresentative: LegalRepresentative;

  // Documentos
  documents: DocumentsInfo;

  // Observaciones
  observations: string;
}

/**
 * Estado de errores del formulario
 */
export interface FormErrors {
  commercialName?: string;
  documentType?: string;
  documentNumber?: string;
  establishmentType?: string;
  phone?: string;
  email?: string;
  departmentId?: string;
  cityId?: string;
  neighborhood?: string;
  mainAddress?: string;
  legalRepresentativeFirstName?: string;
  legalRepresentativeLastName?: string;
  legalRepresentativeDocumentType?: string;
  legalRepresentativeDocumentNumber?: string;
  legalRepresentativeDocumentFile?: string;
  identity?: string;
  commerceChamber?: string;
  observations?: string;
}

/**
 * Estado de carga (para cuando se envía el formulario)
 */
export interface FormLoadingState {
  isLoading: boolean;
  error: string | null;
}

/**
 * Estado inicial del formulario
 */
export const INITIAL_BASIC_INFO_FORM: BasicInfoFormData = {
  commercialName: '',
  documentType: '',
  documentNumber: '',
  establishmentType: '',
  phone: '',
  email: '',
  departmentId: null,
  cityId: null,
  neighborhood: '',
  mainAddress: '',

  legalRepresentative: {
    firstName: '',
    lastName: '',
    documentType: '',
    documentNumber: '',
    documentFile: null,
  },

  documents: {
    identity: null,
    commerceChamber: null,
  },

  observations: '',
};

/**
 * Opciones de selects
 */
export const DOCUMENT_TYPE_OPTIONS = [
  { value: 'nit', label: 'NIT' },
  { value: 'cc', label: 'Cédula de Ciudadanía' },
  { value: 'ce', label: 'Cédula de Extranjería' },
  { value: 'passport', label: 'Pasaporte' },
];

export const ESTABLISHMENT_TYPE_OPTIONS = [
  { value: 'restaurant', label: 'Restaurante' },
  { value: 'cafeteria', label: 'Cafetería' },
  { value: 'bakery', label: 'Panadería' },
  { value: 'fast_food', label: 'Comida Rápida' },
  { value: 'desserts', label: 'Postres' },
  { value: 'drinks', label: 'Bebidas' },
  { value: 'other', label: 'Otro' },
];

export const LEGAL_REP_DOCUMENT_TYPE_OPTIONS = [
  { value: 'cc', label: 'Cédula de Ciudadanía' },
  { value: 'ce', label: 'Cédula de Extranjería' },
  { value: 'passport', label: 'Pasaporte' },
];
