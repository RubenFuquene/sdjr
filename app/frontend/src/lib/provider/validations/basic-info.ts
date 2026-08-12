import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';
import { FRANCHISE_ELIGIBLE_ESTABLISHMENT_CODES } from '@/types/basic-info';
import type { EstablishmentType } from '@/lib/api/establishment-types';

/**
 * SCRUM-365 (CR-01): mismo criterio que el backend — obligatoria para
 * RE/PA, irrelevante para el resto. `establishmentTypes` puede venir vacío
 * mientras el catálogo carga; en ese caso no se valida (evita un error
 * fantasma antes de que el fetch resuelva).
 */
export const validateBasicInfoForm = (
  formData: BasicInfoFormData,
  establishmentTypes: EstablishmentType[] = []
): FormErrors => {
  const newErrors: FormErrors = {};

  if (!formData.commercialName?.trim()) {
    newErrors.commercialName = 'El nombre comercial es obligatorio';
  }

  if (!formData.documentType) {
    newErrors.documentType = 'Debe seleccionar un tipo de documento';
  }

  if (!formData.documentNumber?.trim()) {
    newErrors.documentNumber = 'El número de documento es obligatorio';
  } else if (!/^[\d\-]+$/.test(formData.documentNumber)) {
    newErrors.documentNumber = 'El número de documento solo debe contener números y guiones';
  }

  if (!formData.establishmentType) {
    newErrors.establishmentType = 'Debe seleccionar un tipo de establecimiento';
  }

  if (!formData.phone?.trim()) {
    newErrors.phone = 'El teléfono es obligatorio';
  } else if (!/^\d{10}$/.test(formData.phone)) {
    newErrors.phone = 'El teléfono debe tener exactamente 10 dígitos';
  }

  if (!formData.email?.trim()) {
    newErrors.email = 'El correo electrónico es obligatorio';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
    newErrors.email = 'El correo electrónico no es válido';
  }

  if (!formData.departmentId) {
    newErrors.departmentId = 'Debe seleccionar un departamento';
  }

  if (!formData.cityId) {
    newErrors.cityId = 'Debe seleccionar una ciudad';
  }

  if (!formData.neighborhood?.trim()) {
    newErrors.neighborhood = 'El barrio es obligatorio';
  }

  if (!formData.mainAddress?.trim()) {
    newErrors.mainAddress = 'La dirección principal es obligatoria';
  }

  if (!formData.legalRepresentative.firstName?.trim()) {
    newErrors.legalRepresentativeFirstName = 'El nombre del representante es obligatorio';
  }

  if (!formData.legalRepresentative.lastName?.trim()) {
    newErrors.legalRepresentativeLastName = 'El apellido del representante es obligatorio';
  }

  if (!formData.legalRepresentative.documentType) {
    newErrors.legalRepresentativeDocumentType = 'Debe seleccionar un tipo de documento';
  }

  if (!formData.legalRepresentative.documentNumber?.trim()) {
    newErrors.legalRepresentativeDocumentNumber = 'El número de documento es obligatorio';
  } else if (!/^\d+$/.test(formData.legalRepresentative.documentNumber)) {
    newErrors.legalRepresentativeDocumentNumber = 'El número de documento solo debe contener dígitos';
  }

  if (!formData.legalRepresentative.documentFile?.trim()) {
    newErrors.legalRepresentativeDocumentFile = 'El documento del representante es obligatorio';
  }

  if (!formData.documents.commerceChamber?.trim()) {
    newErrors.commerceChamber = 'El documento de Cámara de Comercio es obligatorio';
  }

  if (formData.electronicInvoicingRequired === null) {
    newErrors.electronicInvoicingRequired = 'Debes indicar si estás obligado a emitir factura electrónica';
  }

  const selectedEstablishmentType = establishmentTypes.find(
    (type) => String(type.id) === formData.establishmentType
  );

  if (
    selectedEstablishmentType &&
    FRANCHISE_ELIGIBLE_ESTABLISHMENT_CODES.includes(selectedEstablishmentType.code) &&
    formData.operatesUnderFranchise === null
  ) {
    newErrors.operatesUnderFranchise = 'Debes indicar si operas bajo franquicia';
  }

  if (!formData.documents.rut?.trim()) {
    newErrors.rut = 'El RUT es obligatorio';
  }

  if (formData.electronicInvoicingRequired === true && !formData.documents.form1876?.trim()) {
    newErrors.form1876 = 'El formato 1876 es obligatorio para comercios obligados a facturar electrónicamente';
  }

  return newErrors;
};
