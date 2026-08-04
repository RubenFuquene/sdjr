import type { ProductBranchAssignment, ProductType } from "@/types/products";
import { parseDecimal } from "./product-form.utils";

export interface ProductFormValidationErrors {
  title?: string;
  productCategoryId?: string;
  originalPrice?: string;
  discountedPrice?: string;
  branches?: string;
  packageItems?: string;
}

type ProductFormValidationInput = {
  title: string;
  productCategoryId: string;
  originalPrice: string;
  discountedPrice: string;
  branches: ProductBranchAssignment[];
  productType: ProductType;
  packageItems: Array<{ productId: number; quantity: number }>;
  /** SCRUM-361: máximo de packs ofrecibles por sede, según el stock de los componentes elegidos en esa sede. */
  maxPacksByBranch?: Map<number, number>;
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

  // SCRUM-361: ambos tipos de producto se asignan a sedes con la misma
  // estructura — para individuales, quantityAvailable es stock físico; para
  // packs, es el compromiso de packs en esa sede.
  if (input.branches.length === 0) {
    nextErrors.branches =
      input.productType === "package" ? "Selecciona la sede del pack." : "Asigna al menos una sucursal.";
  } else if (input.productType === "package" && input.branches.length > 1) {
    // Ajuste funcional 2026-08-03: un pack vive en una sola sede. El
    // selector ya lo impide (selección única); esto es defensa en
    // profundidad por si algo más arriba llega a construir el estado a mano.
    nextErrors.branches = "Un pack solo puede ofrecerse en una sede. Usa \"Duplicar\" para otra sede.";
  } else if (
    input.branches.some((branch) => !Number.isInteger(branch.quantityAvailable) || branch.quantityAvailable < 0)
  ) {
    nextErrors.branches = "Cada sede debe tener una cantidad válida (entero, mayor o igual a 0).";
  } else if (input.branches.some((branch) => branch.isPublished && branch.quantityAvailable === 0)) {
    nextErrors.branches = "No puedes publicar una sede sin inventario/compromiso cargado.";
  } else if (
    input.productType === "package" &&
    input.maxPacksByBranch &&
    input.branches.some((branch) => branch.quantityAvailable > (input.maxPacksByBranch!.get(branch.branchId) ?? Infinity))
  ) {
    nextErrors.branches =
      "La cantidad de packs en una de las sedes supera el máximo disponible según el stock de los componentes en esa sede.";
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

  return nextErrors;
}
