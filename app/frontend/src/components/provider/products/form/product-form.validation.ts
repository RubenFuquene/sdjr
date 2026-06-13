import type { ProductType } from "@/types/products";
import { parseDecimal, parseInteger } from "./product-form.utils";

export interface ProductFormValidationErrors {
  title?: string;
  productCategoryId?: string;
  originalPrice?: string;
  discountedPrice?: string;
  quantityAvailable?: string;
  branchId?: string;
  packageItems?: string;
}

type ProductFormValidationInput = {
  title: string;
  productCategoryId: string;
  originalPrice: string;
  discountedPrice: string;
  quantityAvailable: string;
  branchId: string;
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
  if (input.discountedPrice && (parsedDiscountPrice === null || parsedDiscountPrice < 0)) {
    nextErrors.discountedPrice = "Ingresa un descuento válido.";
  }

  if (
    parsedOriginalPrice !== null &&
    parsedDiscountPrice !== null &&
    parsedDiscountPrice > parsedOriginalPrice
  ) {
    nextErrors.discountedPrice = "El descuento no puede ser mayor al precio original.";
  }

  const parsedQuantityAvailable = parseInteger(input.quantityAvailable);
  if (parsedQuantityAvailable === null || parsedQuantityAvailable < 0) {
    nextErrors.quantityAvailable = "Ingresa una cantidad disponible válida.";
  }

  if (!input.branchId) {
    nextErrors.branchId = "Selecciona una sucursal.";
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
