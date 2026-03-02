"use client";

import { useCallback, useState } from "react";
import {
  ApiError,
  createCommerceBranch,
  getMyCommerce,
  type CommerceBranchFromAPI,
  type CreateCommerceBranchPayload,
  type CreateCommerceBranchHourInput,
} from "@/lib/api";

type ApiValidationErrors = Record<string, string[] | string>;

export type BranchFormFieldErrors = Record<string, string>;

export interface CreateProviderBranchFormInput {
  departmentId: number;
  cityId: number;
  neighborhoodId: number;
  name: string;
  address: string;
  phone?: string | null;
  email?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  status?: boolean;
  hours: CreateCommerceBranchHourInput[];
}

interface SubmitOptions {
  onSuccess?: (branch: CommerceBranchFromAPI) => Promise<void> | void;
}

interface UseCreateProviderBranchReturn {
  submitting: boolean;
  error: string | null;
  fieldErrors: BranchFormFieldErrors;
  submit: (
    input: CreateProviderBranchFormInput,
    options?: SubmitOptions
  ) => Promise<CommerceBranchFromAPI | null>;
  clearErrors: () => void;
}

function extractValidationErrors(data: unknown): BranchFormFieldErrors {
  if (!data || typeof data !== "object") {
    return {};
  }

  const possibleErrors = (data as { errors?: unknown }).errors;
  if (!possibleErrors || typeof possibleErrors !== "object") {
    return {};
  }

  const parsed: BranchFormFieldErrors = {};
  const entries = Object.entries(possibleErrors as ApiValidationErrors);

  for (const [field, value] of entries) {
    if (Array.isArray(value) && value.length > 0) {
      parsed[field] = String(value[0]);
      continue;
    }

    if (typeof value === "string" && value.trim().length > 0) {
      parsed[field] = value;
    }
  }

  return parsed;
}

function toCreatePayload(
  commerceId: number,
  input: CreateProviderBranchFormInput
): CreateCommerceBranchPayload {
  return {
    commerce_branch: {
      commerce_id: commerceId,
      department_id: input.departmentId,
      city_id: input.cityId,
      neighborhood_id: input.neighborhoodId,
      name: input.name,
      address: input.address,
      latitude: input.latitude ?? null,
      longitude: input.longitude ?? null,
      phone: input.phone ?? null,
      email: input.email ?? null,
      status: input.status ?? true,
    },
    commerce_branch_hours: input.hours,
    commerce_branch_photos: [],
  };
}

/**
 * Hook para crear sucursales del proveedor autenticado.
 * Flujo: /api/v1/me/commerce -> /api/v1/commerce-branches (POST)
 */
export function useCreateProviderBranch(): UseCreateProviderBranchReturn {
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<BranchFormFieldErrors>({});

  const clearErrors = useCallback(() => {
    setError(null);
    setFieldErrors({});
  }, []);

  const submit = useCallback(
    async (
      input: CreateProviderBranchFormInput,
      options?: SubmitOptions
    ): Promise<CommerceBranchFromAPI | null> => {
      try {
        setSubmitting(true);
        clearErrors();

        const myCommerceResponse = await getMyCommerce();
        const commerceId = myCommerceResponse.data?.id;

        if (!commerceId) {
          setError("No encontramos un comercio asociado a tu cuenta.");
          return null;
        }

        const payload = toCreatePayload(commerceId, input);
        const response = await createCommerceBranch(payload);
        const createdBranch = response.data;

        if (options?.onSuccess) {
          await options.onSuccess(createdBranch);
        }

        return createdBranch;
      } catch (err) {
        if (err instanceof ApiError) {
          if (err.status === 403) {
            setError("No tienes permisos para crear sucursales (provider.commerces.create).");
            return null;
          }

          if (err.status === 422) {
            setFieldErrors(extractValidationErrors(err.data));
            setError("Por favor valida los campos del formulario.");
            return null;
          }

          setError(err.message);
          return null;
        }

        setError("Error inesperado al crear la sucursal.");
        return null;
      } finally {
        setSubmitting(false);
      }
    },
    [clearErrors]
  );

  return {
    submitting,
    error,
    fieldErrors,
    submit,
    clearErrors,
  };
}
