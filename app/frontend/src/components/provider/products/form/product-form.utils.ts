import type { ProductType } from "@/types/products";
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
    packageItems,
  } = params;

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
    branchId: Number(branchId),
    packageItems: productType === "package" ? packageItems : [],
    photos: [],
  };
}
