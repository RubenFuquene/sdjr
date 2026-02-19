import type { BasicInfoFormData, FormErrors } from '@/types/basic-info';

export const validateBasicInfoForm = (formData: BasicInfoFormData): FormErrors => {
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

  return newErrors;
};
