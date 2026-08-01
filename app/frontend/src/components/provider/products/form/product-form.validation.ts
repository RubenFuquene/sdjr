import type { ProductBranchAssignment, ProductType } from "@/types/products";
import { parseDecimal, parseInteger } from "./product-form.utils";

export interface ProductFormValidationErrors {
  title?: string;
  productCategoryId?: string;
  originalPrice?: string;
  discountedPrice?: string;
  quantityAvailable?: string;
  branchId?: string;
  branches?: string;
  packageItems?: string;
}

type ProductFormValidationInput = {
  title: string;
  productCategoryId: string;
  originalPrice: string;
  discountedPrice: string;
  quantityAvailable: string;
  branchId: string;
  branches: ProductBranchAssignment[];
  productType: ProductType;
  packageItems: Array<{ productId: number; quantity: number }>;
  maxPacks?: number;
};

export function validateProductForm(
  input: ProductFormValidationInput
): ProductFormValidationErrors {
  const nextErrors: ProductFormValidationErrors = {};

  if (!input.title.trim()) {
    nextErrors.title = "El nombre del producto es obligatorio.";
  }

  if (!input.productCategoryId) {
    nextErrors.productCategoryId = "Selecciona una categoría.";
  }

  const parsedOriginalPrice = parseDecimal(input.originalPrice);
  if (parsedOriginalPrice === null || parsedOriginalPrice < 0) {
    nextErrors.originalPrice = "Ingresa un precio válido.";
  }

  const parsedDiscountPrice = parseDecimal(input.discountedPrice);

  if (input.productType !== "package") {
    if (!input.discountedPrice) {
      nextErrors.discountedPrice = "El precio con descuento es obligatorio.";
    } else if (parsedDiscountPrice === null || parsedDiscountPrice <= 0) {
      nextErrors.discountedPrice = "Ingresa un descuento válido, mayor a 0.";
    }
  } else if (input.discountedPrice && (parsedDiscountPrice === null || parsedDiscountPrice < 0)) {
    nextErrors.discountedPrice = "Ingresa un descuento válido.";
  }

  if (
    !nextErrors.discountedPrice &&
    parsedOriginalPrice !== null &&
    parsedDiscountPrice !== null &&
    parsedDiscountPrice > parsedOriginalPrice
  ) {
    nextErrors.discountedPrice = "El descuento no puede ser mayor al precio original.";
  }

  // SCRUM-277 Fase 1: los packs conservan cantidad global + una sola sede
  // (comportamiento anterior, sin cambios); los productos individuales pasan
  // a asignación multi-sede con inventario propio por sede — ver más abajo.
  const parsedQuantityAvailable = parseInteger(input.quantityAvailable);

  if (input.productType === "package") {
    if (parsedQuantityAvailable === null || parsedQuantityAvailable < 0) {
      nextErrors.quantityAvailable = "Ingresa una cantidad disponible válida.";
    }

    if (!input.branchId) {
      nextErrors.branchId = "Selecciona una sucursal.";
    }
  } else {
    if (input.branches.length === 0) {
      nextErrors.branches = "Asigna al menos una sucursal.";
    } else if (
      input.branches.some((branch) => !Number.isInteger(branch.quantityAvailable) || branch.quantityAvailable < 0)
    ) {
      nextErrors.branches = "Cada sucursal debe tener una cantidad válida (entero, mayor o igual a 0).";
    } else if (input.branches.some((branch) => branch.isPublished && branch.quantityAvailable === 0)) {
      nextErrors.branches = "No puedes publicar una sucursal sin inventario cargado.";
    }
  }

  if (input.productType === "package" && input.packageItems.length === 0) {
    nextErrors.packageItems = "Selecciona al menos un producto para el pack.";
  }

  if (
    input.productType === "package" &&
    input.packageItems.some((item) => !Number.isInteger(item.quantity) || item.quantity < 1)
  ) {
    nextErrors.packageItems = "Cada item del pack debe tener una cantidad valida mayor o igual a 1.";
  }

  if (
    input.productType === "package" &&
    input.maxPacks !== undefined &&
    parsedQuantityAvailable !== null &&
    parsedQuantityAvailable > input.maxPacks
  ) {
    nextErrors.quantityAvailable = `La cantidad de paquetes no puede superar el máximo disponible (${input.maxPacks}) según el stock de los productos seleccionados.`;
  }

  return nextErrors;
}
