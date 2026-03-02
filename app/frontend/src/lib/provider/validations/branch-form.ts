import type { BranchFormFieldErrors } from "@/hooks/provider/use-create-provider-branch";

export interface BranchFormValidationInput {
  name: string;
  departmentId: number | null;
  cityId: number | null;
  neighborhoodId: number | null;
  address: string;
  phone: string;
  email: string;
  hours: Array<{
    day_of_week: number;
    open_time: string;
    close_time: string;
  }>;
}

const TIME_PATTERN = /^([01]\d|2[0-3]):[0-5]\d$/;

export const validateBranchForm = (
  formData: BranchFormValidationInput
): BranchFormFieldErrors => {
  const newErrors: BranchFormFieldErrors = {};

  if (!formData.name.trim()) {
    newErrors["commerce_branch.name"] = "El nombre de la sede es obligatorio";
  }

  if (!formData.departmentId) {
    newErrors["commerce_branch.department_id"] = "Debe seleccionar un departamento";
  }

  if (!formData.cityId) {
    newErrors["commerce_branch.city_id"] = "Debe seleccionar una ciudad";
  }

  if (!formData.neighborhoodId) {
    newErrors["commerce_branch.neighborhood_id"] = "Debe seleccionar un barrio";
  }

  if (!formData.address.trim()) {
    newErrors["commerce_branch.address"] = "La dirección es obligatoria";
  }

  if (formData.phone.trim() && !/^\d{10}$/.test(formData.phone.trim())) {
    newErrors["commerce_branch.phone"] = "El teléfono debe tener exactamente 10 dígitos";
  }

  if (
    formData.email.trim() &&
    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email.trim())
  ) {
    newErrors["commerce_branch.email"] = "El correo electrónico no es válido";
  }

  if (formData.hours.length === 0) {
    newErrors["commerce_branch_hours"] = "Debes seleccionar al menos un día con horario";
  }

  formData.hours.forEach((hour, index) => {
    if (!TIME_PATTERN.test(hour.open_time)) {
      newErrors[`commerce_branch_hours.${index}.open_time`] = "Hora de apertura inválida";
    }

    if (!TIME_PATTERN.test(hour.close_time)) {
      newErrors[`commerce_branch_hours.${index}.close_time`] = "Hora de cierre inválida";
    }

    if (
      TIME_PATTERN.test(hour.open_time) &&
      TIME_PATTERN.test(hour.close_time) &&
      hour.open_time >= hour.close_time
    ) {
      newErrors[`commerce_branch_hours.${index}.close_time`] =
        "La hora de cierre debe ser mayor a la de apertura";
    }
  });

  return newErrors;
};
