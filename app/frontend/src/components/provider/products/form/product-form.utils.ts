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
  branches?: ProductBranchAssignment[];
  packageItems?: Array<{ productId: number; quantity: number }>;
};

export type ProductFormDraft = {
  title: string;
  productType: ProductType;
  productCategoryId: string;
  originalPrice: string;
  discountedPrice: string;
  description: string;
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

/**
 * SCRUM-361/323, ticket derivado (2026-08-04): prorrateo P3 del descuento
 * opcional del pack hacia cada componente — precio_línea = precio_descontado
 * × (precio_pack_con_descuento ÷ techo_del_pack). Escala uniformemente cada
 * componente ya descontado por el mismo factor, así que preserva la
 * estructura relativa de descuentos entre componentes.
 *
 * Sin descuento propio del pack (o techo en 0), el factor es 1: el precio
 * dentro del pack es el mismo precio con descuento del componente — no es un
 * caso especial, es el caso normal.
 *
 * Única implementación: la usan tanto el selector de componentes como su
 * resumen agregado (Tarea 3), y es la misma fórmula que aplicará el
 * servidor al explotar order_items (Tarea 4).
 */
export function priceWithinPack(params: {
  componentSalePrice: number;
  packCeiling: number;
  packDiscountedPrice: number | null;
}): number {
  const { componentSalePrice, packCeiling, packDiscountedPrice } = params;

  if (packDiscountedPrice === null || packCeiling <= 0) {
    return componentSalePrice;
  }

  return Number((componentSalePrice * (packDiscountedPrice / packCeiling)).toFixed(2));
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
    description: initialData?.description ?? "",
    branches: initialData?.branches ?? [],
    packageItems: initialData?.packageItems ?? [],
  };
}

type BuildSubmitInputParams = {
  commerceId?: number;
  title: string;
  productCategoryId: string;
  productType: ProductType;
  originalPrice: number;
  discountedPrice: number | null;
  description: string;
  branches: ProductBranchAssignment[];
  packageItems: Array<{ productId: number; quantity: number }>;
};

export function buildProductFormSubmitInput(
  params: BuildSubmitInputParams
): ProviderProductFormInput {
  const {
    commerceId,
    title,
    productCategoryId,
    productType,
    originalPrice,
    discountedPrice,
    description,
    branches,
    packageItems,
  } = params;

  return {
    commerceId,
    title: title.trim(),
    productCategoryId: Number(productCategoryId),
    productType,
    originalPrice,
    discountedPrice,
    description: description.trim() ? description.trim() : null,
    branches,
    packageItems: productType === "package" ? packageItems : [],
    photos: [],
  };
}
