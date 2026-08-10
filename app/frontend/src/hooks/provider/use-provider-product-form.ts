"use client";

import { useCallback, useState } from "react";
import {
  ApiError,
  createProduct,
  createPackageProduct,
  deleteProduct,
  dismissProductBranchAutoAdjustment,
  getMyCommerce,
  mapProductFormToCreatePayload,
  mapProductFormToUpdatePayload,
  updateProduct,
  updatePackageProduct,
} from "@/lib/api";
import type {
  AffectedFiscalBranch,
  AffectedFiscalPackage,
  AffectedPackage,
  ProductFromAPI,
  ProviderProductFormFieldErrors,
  ProviderProductFormInput,
} from "@/types/products";

type ApiValidationErrors = Record<string, string[] | string>;

/** SCRUM-362 (D9): impacto de reclasificar a otro_verificar, del último 409. */
export interface FiscalReclassificationImpact {
  affectedBranches: AffectedFiscalBranch[];
  affectedPackages: AffectedFiscalPackage[];
}

interface UseProviderProductFormReturn {
  submitting: boolean;
  error: string | null;
  fieldErrors: ProviderProductFormFieldErrors;
  /** SCRUM-361, Tarea 3.3: packs afectados por la última edición rechazada con 409. */
  affectedPackages: AffectedPackage[] | null;
  /** SCRUM-362 (D9): sedes/packs que se despublicarían al reclasificar a otro_verificar. */
  fiscalReclassificationImpact: FiscalReclassificationImpact | null;
  createProduct: (input: ProviderProductFormInput) => Promise<ProductFromAPI | null>;
  updateProduct: (productId: number, input: ProviderProductFormInput) => Promise<ProductFromAPI | null>;
  deleteProduct: (productId: number) => Promise<boolean>;
  dismissAutoAdjustment: (productId: number, branchId: number) => Promise<boolean>;
  clearAffectedPackages: () => void;
  clearFiscalReclassificationImpact: () => void;
  resetErrors: () => void;
}

function extractAffectedPackages(data: unknown): AffectedPackage[] | null {
  if (!data || typeof data !== "object") {
    return null;
  }

  const errors = (data as { errors?: unknown }).errors;
  if (!errors || typeof errors !== "object") {
    return null;
  }

  const raw = (errors as { affected_packages?: unknown }).affected_packages;
  if (!Array.isArray(raw) || raw.length === 0) {
    return null;
  }

  return raw
    .filter((item): item is Record<string, unknown> => !!item && typeof item === "object")
    .map((item) => ({
      packageId: Number(item.package_id),
      packageTitle: String(item.package_title ?? ""),
      commerceBranchId: Number(item.commerce_branch_id),
      currentQuantity: Number(item.current_quantity),
      adjustedQuantity: Number(item.adjusted_quantity),
    }));
}

function extractFiscalReclassificationImpact(data: unknown): FiscalReclassificationImpact | null {
  if (!data || typeof data !== "object") {
    return null;
  }

  const errors = (data as { errors?: unknown }).errors;
  if (!errors || typeof errors !== "object") {
    return null;
  }

  const rawBranches = (errors as { affected_branches?: unknown }).affected_branches;
  const rawPackages = (errors as { affected_packages?: unknown }).affected_packages;

  const affectedBranches = Array.isArray(rawBranches)
    ? rawBranches
        .filter((item): item is Record<string, unknown> => !!item && typeof item === "object")
        .map((item) => ({
          commerceBranchId: Number(item.commerce_branch_id),
          commerceBranchName: String(item.commerce_branch_name ?? ""),
        }))
    : [];

  const affectedPackages = Array.isArray(rawPackages)
    ? rawPackages
        .filter((item): item is Record<string, unknown> => !!item && typeof item === "object")
        .map((item) => ({
          packageId: Number(item.package_id),
          packageTitle: String(item.package_title ?? ""),
        }))
    : [];

  if (affectedBranches.length === 0 && affectedPackages.length === 0) {
    return null;
  }

  return { affectedBranches, affectedPackages };
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

  if (err.status === 409) {
    return "Este cambio requiere confirmación. Revisa el detalle antes de continuar.";
  }

  return err.message || "No pudimos procesar la solicitud de producto.";
}

export function useProviderProductForm(): UseProviderProductFormReturn {
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<ProviderProductFormFieldErrors>({});
  const [affectedPackages, setAffectedPackages] = useState<AffectedPackage[] | null>(null);
  const [fiscalReclassificationImpact, setFiscalReclassificationImpact] =
    useState<FiscalReclassificationImpact | null>(null);

  const resetErrors = useCallback(() => {
    setError(null);
    setFieldErrors({});
    setAffectedPackages(null);
    setFiscalReclassificationImpact(null);
  }, []);

  const clearAffectedPackages = useCallback(() => {
    setAffectedPackages(null);
  }, []);

  const clearFiscalReclassificationImpact = useCallback(() => {
    setFiscalReclassificationImpact(null);
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

        const response =
          input.productType === "package"
            ? await createPackageProduct(payload)
            : await createProduct(payload);
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

        const response =
          input.productType === "package"
            ? await updatePackageProduct(productId, payload)
            : await updateProduct(productId, payload);
        return response.data;
      } catch (err) {
        if (err instanceof ApiError) {
          if (err.status === 422) {
            setFieldErrors(extractValidationErrors(err.data));
          }

          if (err.status === 409) {
            setAffectedPackages(extractAffectedPackages(err.data));
            setFiscalReclassificationImpact(extractFiscalReclassificationImpact(err.data));
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

  const dismissAutoAdjustmentAction = useCallback(
    async (productId: number, branchId: number): Promise<boolean> => {
      try {
        await dismissProductBranchAutoAdjustment(productId, branchId);
        return true;
      } catch (err) {
        if (err instanceof ApiError) {
          setError(mapApiErrorToMessage(err));
          return false;
        }

        setError("Error inesperado al descartar el aviso.");
        return false;
      }
    },
    []
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
    affectedPackages,
    fiscalReclassificationImpact,
    createProduct: createProductAction,
    updateProduct: updateProductAction,
    deleteProduct: deleteProductAction,
    dismissAutoAdjustment: dismissAutoAdjustmentAction,
    clearAffectedPackages,
    clearFiscalReclassificationImpact,
    resetErrors,
  };
}
