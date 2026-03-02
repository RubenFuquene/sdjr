"use client";

import { useCallback, useState } from "react";
import {
  ApiError,
  createProduct,
  deleteProduct,
  getMyCommerce,
  mapProductFormToCreatePayload,
  mapProductFormToUpdatePayload,
  updateProduct,
  type ProductFormInput,
  type ProductFromAPI,
} from "@/lib/api";

type ApiValidationErrors = Record<string, string[] | string>;

export type ProviderProductFormFieldErrors = Record<string, string>;

export type ProviderProductFormInput = Omit<ProductFormInput, "commerceId"> & {
  commerceId?: number;
};

interface UseProviderProductFormReturn {
  submitting: boolean;
  error: string | null;
  fieldErrors: ProviderProductFormFieldErrors;
  createProduct: (input: ProviderProductFormInput) => Promise<ProductFromAPI | null>;
  updateProduct: (productId: number, input: ProviderProductFormInput) => Promise<ProductFromAPI | null>;
  deleteProduct: (productId: number) => Promise<boolean>;
  resetErrors: () => void;
}

function extractValidationErrors(data: unknown): ProviderProductFormFieldErrors {
  if (!data || typeof data !== "object") {
    return {};
  }

  const possibleErrors = (data as { errors?: unknown }).errors;
  if (!possibleErrors || typeof possibleErrors !== "object") {
    return {};
  }

  const parsed: ProviderProductFormFieldErrors = {};
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

async function resolveCommerceId(inputCommerceId?: number): Promise<number | null> {
  if (typeof inputCommerceId === "number" && Number.isFinite(inputCommerceId) && inputCommerceId > 0) {
    return inputCommerceId;
  }

  const myCommerceResponse = await getMyCommerce();
  return myCommerceResponse.data?.id ?? null;
}

function mapApiErrorToMessage(err: ApiError): string {
  if (err.status === 401) {
    return "Tu sesión expiró o no es válida. Inicia sesión para continuar.";
  }

  if (err.status === 403) {
    return "No tienes permisos para gestionar productos del proveedor.";
  }

  if (err.status === 422) {
    return "Por favor valida los campos del formulario.";
  }

  return err.message || "No pudimos procesar la solicitud de producto.";
}

export function useProviderProductForm(): UseProviderProductFormReturn {
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<ProviderProductFormFieldErrors>({});

  const resetErrors = useCallback(() => {
    setError(null);
    setFieldErrors({});
  }, []);

  const createProductAction = useCallback(
    async (input: ProviderProductFormInput): Promise<ProductFromAPI | null> => {
      try {
        setSubmitting(true);
        resetErrors();

        const commerceId = await resolveCommerceId(input.commerceId);
        if (!commerceId) {
          setError("No encontramos un comercio asociado a tu cuenta.");
          return null;
        }

        const payload = mapProductFormToCreatePayload({
          ...input,
          commerceId,
        });

        const response = await createProduct(payload);
        return response.data;
      } catch (err) {
        if (err instanceof ApiError) {
          if (err.status === 422) {
            setFieldErrors(extractValidationErrors(err.data));
          }

          setError(mapApiErrorToMessage(err));
          return null;
        }

        setError("Error inesperado al crear el producto.");
        return null;
      } finally {
        setSubmitting(false);
      }
    },
    [resetErrors]
  );

  const updateProductAction = useCallback(
    async (productId: number, input: ProviderProductFormInput): Promise<ProductFromAPI | null> => {
      try {
        setSubmitting(true);
        resetErrors();

        const commerceId = await resolveCommerceId(input.commerceId);
        if (!commerceId) {
          setError("No encontramos un comercio asociado a tu cuenta.");
          return null;
        }

        const payload = mapProductFormToUpdatePayload({
          ...input,
          commerceId,
        });

        const response = await updateProduct(productId, payload);
        return response.data;
      } catch (err) {
        if (err instanceof ApiError) {
          if (err.status === 422) {
            setFieldErrors(extractValidationErrors(err.data));
          }

          setError(mapApiErrorToMessage(err));
          return null;
        }

        setError("Error inesperado al actualizar el producto.");
        return null;
      } finally {
        setSubmitting(false);
      }
    },
    [resetErrors]
  );

  const deleteProductAction = useCallback(
    async (productId: number): Promise<boolean> => {
      try {
        setSubmitting(true);
        resetErrors();

        await deleteProduct(productId);
        return true;
      } catch (err) {
        if (err instanceof ApiError) {
          setError(mapApiErrorToMessage(err));
          return false;
        }

        setError("Error inesperado al eliminar el producto.");
        return false;
      } finally {
        setSubmitting(false);
      }
    },
    [resetErrors]
  );

  return {
    submitting,
    error,
    fieldErrors,
    createProduct: createProductAction,
    updateProduct: updateProductAction,
    deleteProduct: deleteProductAction,
    resetErrors,
  };
}
