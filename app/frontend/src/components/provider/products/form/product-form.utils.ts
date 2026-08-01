import type { ProductBranchAssignment, ProductType } from "@/types/products";
import type { ProviderProductFormInput } from "@/types/products";

type ProductFormInitialLike = {
  commerceId?: number;
  title?: string;
  description?: string | null;
  productType?: "single" | "package";
  productCategoryId?: number;
  originalPrice?: number;
  discountedPrice?: number | null;
  quantityAvailable?: number;
  quantityTotal?: number;
  branchId?: number | null;
  branches?: ProductBranchAssignment[];
  packageItems?: Array<{ productId: number; quantity: number }>;
};

export type ProductFormDraft = {
  title: string;
  productType: ProductType;
  productCategoryId: string;
  originalPrice: string;
  discountedPrice: string;
  quantityAvailable: string;
  description: string;
  branchId: string;
  branches: ProductBranchAssignment[];
  packageItems: Array<{ productId: number; quantity: number }>;
};

export function parseDecimal(value: string): number | null {
  if (!value.trim()) {
    return null;
  }

  const parsed = Number(value.replace(",", "."));
  if (!Number.isFinite(parsed)) {
    return null;
  }

  return parsed;
}

export function parseInteger(value: string): number | null {
  if (!value.trim()) {
    return null;
  }

  const parsed = Number(value);
  if (!Number.isInteger(parsed)) {
    return null;
  }

  return parsed;
}

export function mapInitialDataToDraft(initialData?: ProductFormInitialLike | null): ProductFormDraft {
  return {
    title: initialData?.title ?? "",
    productType: initialData?.productType ?? "single",
    productCategoryId: initialData?.productCategoryId ? String(initialData.productCategoryId) : "",
    originalPrice:
      initialData?.originalPrice !== undefined ? String(initialData.originalPrice) : "",
    discountedPrice:
      initialData?.discountedPrice !== undefined && initialData?.discountedPrice !== null
        ? String(initialData.discountedPrice)
        : "",
    quantityAvailable:
      initialData?.quantityAvailable !== undefined ? String(initialData.quantityAvailable) : "",
    description: initialData?.description ?? "",
    branchId: initialData?.branchId ? String(initialData.branchId) : "",
    branches: initialData?.branches ?? [],
    packageItems: initialData?.packageItems ?? [],
  };
}

type BuildSubmitInputParams = {
  commerceId?: number;
  quantityTotal?: number;
  title: string;
  productCategoryId: string;
  productType: ProductType;
  originalPrice: number;
  discountedPrice: number | null;
  quantityAvailable: number;
  description: string;
  branchId: string;
  branches: ProductBranchAssignment[];
  packageItems: Array<{ productId: number; quantity: number }>;
};

export function buildProductFormSubmitInput(
  params: BuildSubmitInputParams
): ProviderProductFormInput {
  const {
    commerceId,
    quantityTotal,
    title,
    productCategoryId,
    productType,
    originalPrice,
    discountedPrice,
    quantityAvailable,
    description,
    branchId,
    branches,
    packageItems,
  } = params;

  // SCRUM-277 Fase 1: los packs conservan una sola sede (comportamiento
  // anterior, sin inventario por sede propio todavía — Fase 2 lo migra); los
  // individuales envían la asignación multi-sede completa.
  const submittedBranches: ProductBranchAssignment[] =
    productType === "package"
      ? branchId
        ? [{ branchId: Number(branchId), quantityAvailable: 0, isPublished: false }]
        : []
      : branches;

  return {
    commerceId,
    title: title.trim(),
    productCategoryId: Number(productCategoryId),
    productType,
    originalPrice,
    discountedPrice,
    quantityAvailable,
    quantityTotal: quantityTotal ?? quantityAvailable,
    description: description.trim() ? description.trim() : null,
    branches: submittedBranches,
    packageItems: productType === "package" ? packageItems : [],
    photos: [],
  };
}
