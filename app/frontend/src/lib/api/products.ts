/**
 * Products (Productos) API Module
 * Provider products listing + product form payload mappers (create/update)
 */

import { fetchWithErrorHandling } from "./client";
import type { ApiSuccess } from "./types";
import type {
  FiscalCodeOption,
  ProductBranchAssignment,
  ProductCategoryFromAPI,
  ProductFormInput,
  ProductFromAPI,
  ProductType,
} from "@/types/products";

// Backward compatibility: maintain temporary type exports from API layer.
export type {
  FiscalCodeOption,
  ProductCategoryFromAPI,
  ProductFormInput,
  ProductFromAPI,
  ProductPhotoFromAPI,
  ProductType,
} from "@/types/products";

export interface GetProductCategoriesParams {
  page?: number;
  perPage?: number;
  status?: string;
  search?: string;
}

export interface CreateProductPhotoInput {
  file_name: string;
  mime_type: string;
  file_size_bytes: number;
  versioning_enabled?: string;
  metadata?: Record<string, unknown>;
}

export interface CommerceBranchAssignmentPayload {
  commerce_branch_id: number;
  quantity_available: number;
  is_published?: boolean;
}

export interface CreateProductPayload {
  product: {
    commerce_id: number;
    /** SCRUM-370: ausente para packs — el backend la deriva de sus componentes. */
    product_category_id?: number;
    /** SCRUM-362: ausente para packs — nunca llevan clasificación propia. */
    fiscal_code?: string | null;
    title: string;
    description?: string | null;
    product_type: ProductType;
    original_price: number;
    discounted_price?: number | null;
    expires_at?: string | null;
    status: string;
  };
  commerce_branches?: CommerceBranchAssignmentPayload[];
  package_items?: Array<{
    product_id: number;
    quantity: number;
  }>;
  photos?: CreateProductPhotoInput[];
}

export interface UpdateProductPayload {
  product: {
    commerce_id: number;
    product_category_id?: number;
    fiscal_code?: string | null;
    title?: string;
    description?: string | null;
    product_type?: ProductType;
    original_price?: number;
    discounted_price?: number | null;
    expires_at?: string | null;
    status?: string;
  };
  commerce_branches?: CommerceBranchAssignmentPayload[];
  package_items?: Array<{
    product_id: number;
    quantity: number;
  }>;
  photos?: CreateProductPhotoInput[];
  /** SCRUM-361, Tarea 3.3-3.4: confirma el ajuste automático de packs afectados. */
  confirm_package_adjustments?: boolean;
  /** SCRUM-362 (D9): confirma despublicar en cascada sedes y packs al reclasificar a otro_verificar. */
  confirm_fiscal_reclassification?: boolean;
}

export interface PackageItemFromAPI extends ProductFromAPI {
  quantity?: number;
  pivot?: {
    quantity?: number;
  };
}

function toNumber(value: number | string | null | undefined, fallback = 0): number {
  if (typeof value === "number" && Number.isFinite(value)) {
    return value;
  }

  if (typeof value === "string") {
    const parsed = Number(value);
    if (Number.isFinite(parsed)) {
      return parsed;
    }
  }

  return fallback;
}

function toInteger(value: number | string | null | undefined, fallback = 0): number {
  return Math.trunc(toNumber(value, fallback));
}

function toTrimmedString(value: string | null | undefined): string | undefined {
  if (typeof value !== "string") {
    return undefined;
  }

  const trimmed = value.trim();
  return trimmed.length > 0 ? trimmed : undefined;
}

function buildQueryString(params: GetProductCategoriesParams): string {
  const query = new URLSearchParams();

  if (params.page !== undefined) query.set("page", String(params.page));
  if (params.perPage !== undefined) query.set("per_page", String(params.perPage));
  if (params.status) query.set("status", params.status);
  if (params.search) query.set("search", params.search);

  const queryString = query.toString();
  return queryString ? `?${queryString}` : "";
}

function extractCollectionData<T>(payload: unknown): T[] {
  if (Array.isArray(payload)) {
    return payload as T[];
  }

  if (
    payload &&
    typeof payload === "object" &&
    "data" in payload &&
    Array.isArray((payload as { data?: unknown }).data)
  ) {
    return (payload as { data: T[] }).data;
  }

  return [];
}

function normalizeProduct(product: ProductFromAPI): ProductFromAPI {
  return {
    ...product,
    id: toInteger(product.id),
    commerce_id: toInteger(product.commerce_id),
    product_category_id: toInteger(product.product_category_id),
    original_price: toNumber(product.original_price),
    discounted_price:
      product.discounted_price === null || product.discounted_price === undefined
        ? null
        : toNumber(product.discounted_price),
    vat_rate:
      product.vat_rate === null || product.vat_rate === undefined
        ? null
        : toNumber(product.vat_rate),
    inc_rate:
      product.inc_rate === null || product.inc_rate === undefined
        ? null
        : toNumber(product.inc_rate),
    photos: product.photos ?? [],
    commerce_branches: product.commerce_branches?.map((branch) => ({
      ...branch,
      id: toInteger(branch.id),
      quantity_available:
        branch.quantity_available === undefined ? undefined : toInteger(branch.quantity_available),
      auto_adjusted_from:
        branch.auto_adjusted_from === null || branch.auto_adjusted_from === undefined
          ? null
          : toInteger(branch.auto_adjusted_from),
      available_for_packaging:
        branch.available_for_packaging === null || branch.available_for_packaging === undefined
          ? null
          : toInteger(branch.available_for_packaging),
    })),
  };
}

function normalizeCategory(category: ProductCategoryFromAPI): ProductCategoryFromAPI {
  return {
    ...category,
    id: toInteger(category.id),
    description: category.description ?? null,
  };
}

function normalizeFiscalCodeOption(option: FiscalCodeOption): FiscalCodeOption {
  return {
    ...option,
    vat_rate: toNumber(option.vat_rate),
    inc_rate: toNumber(option.inc_rate),
  };
}

function normalizePackageItem(item: PackageItemFromAPI): PackageItemFromAPI {
  const normalizedBase = normalizeProduct(item);

  return {
    ...normalizedBase,
    quantity:
      item.quantity !== undefined
        ? toInteger(item.quantity)
        : item.pivot?.quantity !== undefined
          ? toInteger(item.pivot.quantity)
          : undefined,
    pivot:
      item.pivot?.quantity !== undefined
        ? {
            quantity: toInteger(item.pivot.quantity),
          }
        : item.pivot,
  };
}

function normalizePackageItems(
  input: ProductFormInput
): Array<{ product_id: number; quantity: number }> | undefined {
  if (input.productType !== "package") {
    return undefined;
  }

  const packageItems = (input.packageItems ?? [])
    .map((item) => ({
      product_id: toInteger(item.productId),
      quantity: toInteger(item.quantity),
    }))
    .filter((item) => item.product_id > 0 && item.quantity > 0);

  return packageItems.length > 0 ? packageItems : undefined;
}

function normalizePhotos(input: ProductFormInput): CreateProductPhotoInput[] | undefined {
  if (!input.photos || input.photos.length === 0) {
    return undefined;
  }

  return input.photos;
}

function normalizeBranches(
  branches: ProductBranchAssignment[] | undefined
): CommerceBranchAssignmentPayload[] | undefined {
  if (!branches) {
    return undefined;
  }

  return branches
    .filter((branch) => toInteger(branch.branchId) > 0)
    .map((branch) => ({
      commerce_branch_id: toInteger(branch.branchId),
      quantity_available: Math.max(0, toInteger(branch.quantityAvailable)),
      is_published: branch.isPublished,
    }));
}

export function mapProductFormToCreatePayload(input: ProductFormInput): CreateProductPayload {
  const isPackage = input.productType === "package";

  return {
    product: {
      commerce_id: toInteger(input.commerceId),
      // SCRUM-370: un pack no elige categoría, el backend la deriva.
      product_category_id: isPackage ? undefined : toInteger(input.productCategoryId),
      // SCRUM-362: un pack nunca lleva clasificación fiscal propia.
      fiscal_code: isPackage ? undefined : (input.fiscalCode ?? null),
      title: input.title.trim(),
      description: toTrimmedString(input.description) ?? null,
      product_type: input.productType,
      original_price: toNumber(input.originalPrice),
      discounted_price:
        input.discountedPrice === null || input.discountedPrice === undefined
          ? null
          : toNumber(input.discountedPrice),
      expires_at: input.expiresAt ?? null,
      status: input.status ?? "1",
    },
    commerce_branches: normalizeBranches(input.branches),
    package_items: normalizePackageItems(input),
    photos: normalizePhotos(input),
  };
}

export function mapProductFormToUpdatePayload(input: ProductFormInput): UpdateProductPayload {
  const isPackage = input.productType === "package";

  return {
    product: {
      commerce_id: toInteger(input.commerceId),
      product_category_id: isPackage ? undefined : toInteger(input.productCategoryId),
      fiscal_code: isPackage ? undefined : (input.fiscalCode ?? null),
      title: input.title.trim(),
      description: toTrimmedString(input.description) ?? null,
      product_type: input.productType,
      original_price: toNumber(input.originalPrice),
      discounted_price:
        input.discountedPrice === null || input.discountedPrice === undefined
          ? null
          : toNumber(input.discountedPrice),
      expires_at: input.expiresAt ?? null,
      status: input.status ?? "1",
    },
    commerce_branches: normalizeBranches(input.branches),
    package_items: normalizePackageItems(input),
    photos: normalizePhotos(input),
    confirm_package_adjustments: input.confirmPackageAdjustments,
    confirm_fiscal_reclassification: input.confirmFiscalReclassification,
  };
}

/**
 * GET /api/v1/products/commerce/{commerce_id}
 * Lista productos por comercio (provider dashboard)
 */
export async function getProductsByCommerce(
  commerceId: number
): Promise<ApiSuccess<ProductFromAPI[]>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI[]>>(
    `/api/v1/products/commerce/${commerceId}`
  );

  const products = extractCollectionData<ProductFromAPI>(response.data);

  return {
    ...response,
    data: products.map(normalizeProduct),
  };
}

/**
 * GET /api/v1/products/{id}
 * Obtiene detalle de producto por ID
 */
export async function getProductById(
  productId: number
): Promise<ApiSuccess<ProductFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI>>(
    `/api/v1/products/${productId}`
  );

  return {
    ...response,
    data: normalizeProduct(response.data),
  };
}

/**
 * GET /api/v1/products/commerce/package-items/{product_package_id}
 * Obtiene items de un pack por ID
 */
export async function getPackageItemsByProductId(
  productPackageId: number
): Promise<ApiSuccess<PackageItemFromAPI[]>> {
  const response = await fetchWithErrorHandling<
    ApiSuccess<{ package_items?: unknown }>
  >(`/api/v1/products/commerce/package-items/${productPackageId}`);

  // Este endpoint devuelve el producto (pack) completo con los items
  // anidados en `data.package_items`, no una colección plana en `data`.
  const packageItems = extractCollectionData<PackageItemFromAPI>(
    response.data?.package_items
  );

  return {
    ...response,
    data: packageItems.map(normalizePackageItem),
  };
}

/**
 * GET /api/v1/product-categories
 * Obtiene categorías para el formulario de productos
 */
export async function getProductCategories(
  params: GetProductCategoriesParams = { page: 1, perPage: 100 }
): Promise<ApiSuccess<ProductCategoryFromAPI[]>> {
  const queryString = buildQueryString(params);
  const response = await fetchWithErrorHandling<ApiSuccess<unknown>>(
    `/api/v1/product-categories${queryString}`
  );

  const categories = extractCollectionData<ProductCategoryFromAPI>(response.data);

  return {
    ...response,
    data: categories.map(normalizeCategory),
  };
}

/**
 * GET /api/v1/commerces/{id}/fiscal-codes
 * SCRUM-362: códigos fiscales que el comercio puede usar en sus productos,
 * ya filtrados por tipo de establecimiento y declaración de franquicia.
 */
export async function getCommerceFiscalCodes(
  commerceId: number
): Promise<ApiSuccess<FiscalCodeOption[]>> {
  const response = await fetchWithErrorHandling<ApiSuccess<unknown>>(
    `/api/v1/commerces/${commerceId}/fiscal-codes`
  );

  const options = extractCollectionData<FiscalCodeOption>(response.data);

  return {
    ...response,
    data: options.map(normalizeFiscalCodeOption),
  };
}

/**
 * POST /api/v1/products
 * Crea producto o pack
 */
export async function createProduct(
  payload: CreateProductPayload
): Promise<ApiSuccess<ProductFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI>>(
    `/api/v1/products`,
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );

  return {
    ...response,
    data: normalizeProduct(response.data),
  };
}

/**
 * POST /api/v1/products/commerce/package-items
 * Crea pack usando endpoint especializado de backend
 */
export async function createPackageProduct(
  payload: CreateProductPayload
): Promise<ApiSuccess<ProductFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI>>(
    `/api/v1/products/commerce/package-items`,
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );

  return {
    ...response,
    data: normalizeProduct(response.data),
  };
}

/**
 * PUT /api/v1/products/{id}
 * Actualiza producto o pack
 */
export async function updateProduct(
  productId: number,
  payload: UpdateProductPayload
): Promise<ApiSuccess<ProductFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI>>(
    `/api/v1/products/${productId}`,
    {
      method: "PUT",
      body: JSON.stringify(payload),
    }
  );

  return {
    ...response,
    data: normalizeProduct(response.data),
  };
}

/**
 * PUT /api/v1/products/commerce/package-items/{id}
 * Actualiza pack usando endpoint especializado de backend
 */
export async function updatePackageProduct(
  productId: number,
  payload: UpdateProductPayload
): Promise<ApiSuccess<ProductFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI>>(
    `/api/v1/products/commerce/package-items/${productId}`,
    {
      method: "PUT",
      body: JSON.stringify(payload),
    }
  );

  return {
    ...response,
    data: normalizeProduct(response.data),
  };
}

/**
 * DELETE /api/v1/products/{id}
 * Elimina producto o pack
 */
export async function deleteProduct(productId: number): Promise<void> {
  await fetchWithErrorHandling<void>(`/api/v1/products/${productId}`, {
    method: "DELETE",
  });
}

/**
 * PATCH /api/v1/products/{id}/branches/{branchId}
 * Publica o despublica un producto en una sola sede, sin reenviar el
 * producto completo (SCRUM-277 Fase 1, Tarea 3.2/5.1).
 */
export async function updateProductBranchPublication(
  productId: number,
  branchId: number,
  isPublished: boolean
): Promise<ApiSuccess<ProductFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI>>(
    `/api/v1/products/${productId}/branches/${branchId}`,
    {
      method: "PATCH",
      body: JSON.stringify({ is_published: isPublished }),
    }
  );

  return {
    ...response,
    data: normalizeProduct(response.data),
  };
}

/**
 * DELETE /api/v1/products/{id}/branches/{branchId}/auto-adjustment
 * Descarta el aviso de ajuste automático de un pack en una sede, sin
 * tocar su cantidad comprometida (SCRUM-361, Tarea 3.8/6.5).
 */
export async function dismissProductBranchAutoAdjustment(
  productId: number,
  branchId: number
): Promise<ApiSuccess<ProductFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<ProductFromAPI>>(
    `/api/v1/products/${productId}/branches/${branchId}/auto-adjustment`,
    { method: "DELETE" }
  );

  return {
    ...response,
    data: normalizeProduct(response.data),
  };
}
