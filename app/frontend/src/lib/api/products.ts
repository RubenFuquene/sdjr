/**
 * Products (Productos) API Module
 * Provider products listing + product form payload mappers (create/update)
 */

import { fetchWithErrorHandling } from "./client";
import type { DocumentUploadResource } from "./documents";

export type ProductType = "single" | "package";

export interface ProductPhotoFromAPI extends DocumentUploadResource {
  product_id?: number;
}

export interface ProductFromAPI {
  id: number;
  commerce_id: number;
  product_category_id: number;
  title: string;
  description: string | null;
  product_type: ProductType;
  original_price: number;
  discounted_price: number | null;
  quantity_total: number;
  quantity_available: number;
  expires_at: string | null;
  photos?: ProductPhotoFromAPI[];
  status: string;
  created_at: string;
  updated_at: string;
}

export interface ApiSuccess<T> {
  status: boolean;
  message?: string | null;
  data: T;
}

export interface ProductCategoryFromAPI {
  id: number;
  name: string;
  description: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

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

export interface ProductFormInput {
  commerceId: number;
  productCategoryId: number;
  title: string;
  description?: string | null;
  productType: ProductType;
  originalPrice: number;
  discountedPrice?: number | null;
  quantityTotal?: number;
  quantityAvailable: number;
  expiresAt?: string | null;
  status?: string;
  branchId?: number | null;
  packageItemIds?: number[];
  photos?: CreateProductPhotoInput[];
}

export interface CreateProductPayload {
  product: {
    commerce_id: number;
    product_category_id: number;
    title: string;
    description?: string | null;
    product_type: ProductType;
    original_price: number;
    discounted_price?: number | null;
    quantity_total: number;
    quantity_available: number;
    expires_at?: string | null;
    status: string;
  };
  commerce_branch_ids?: number[];
  package_items?: number[];
  photos?: CreateProductPhotoInput[];
}

export interface UpdateProductPayload {
  product: {
    commerce_id: number;
    product_category_id?: number;
    title?: string;
    description?: string | null;
    product_type?: ProductType;
    original_price?: number;
    discounted_price?: number | null;
    quantity_total?: number;
    quantity_available?: number;
    expires_at?: string | null;
    status?: string;
  };
  commerce_branches?: number[];
  package_items?: number[];
  photos?: CreateProductPhotoInput[];
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
    quantity_total: toInteger(product.quantity_total),
    quantity_available: toInteger(product.quantity_available),
    photos: product.photos ?? [],
  };
}

function normalizeCategory(category: ProductCategoryFromAPI): ProductCategoryFromAPI {
  return {
    ...category,
    id: toInteger(category.id),
    description: category.description ?? null,
  };
}

function normalizePackageItems(input: ProductFormInput): number[] | undefined {
  if (input.productType !== "package") {
    return undefined;
  }

  const packageItems = (input.packageItemIds ?? [])
    .map((id) => toInteger(id))
    .filter((id) => id > 0);

  return packageItems.length > 0 ? packageItems : undefined;
}

function normalizePhotos(input: ProductFormInput): CreateProductPhotoInput[] | undefined {
  if (!input.photos || input.photos.length === 0) {
    return undefined;
  }

  return input.photos;
}

function normalizeBranchAsArray(branchId: number | null | undefined): number[] | undefined {
  const normalized = toInteger(branchId);

  if (normalized <= 0) {
    return undefined;
  }

  return [normalized];
}

export function mapProductFormToCreatePayload(input: ProductFormInput): CreateProductPayload {
  const quantityAvailable = toInteger(input.quantityAvailable);
  const quantityTotal = toInteger(input.quantityTotal ?? quantityAvailable, quantityAvailable);

  return {
    product: {
      commerce_id: toInteger(input.commerceId),
      product_category_id: toInteger(input.productCategoryId),
      title: input.title.trim(),
      description: toTrimmedString(input.description) ?? null,
      product_type: input.productType,
      original_price: toNumber(input.originalPrice),
      discounted_price:
        input.discountedPrice === null || input.discountedPrice === undefined
          ? null
          : toNumber(input.discountedPrice),
      quantity_total: quantityTotal,
      quantity_available: quantityAvailable,
      expires_at: input.expiresAt ?? null,
      status: input.status ?? "1",
    },
    commerce_branch_ids: normalizeBranchAsArray(input.branchId),
    package_items: normalizePackageItems(input),
    photos: normalizePhotos(input),
  };
}

/**
 * Contrato transitorio backend:
 * - create usa `commerce_branch_ids`
 * - update usa `commerce_branches`
 */
export function mapProductFormToUpdatePayload(input: ProductFormInput): UpdateProductPayload {
  const quantityAvailable = toInteger(input.quantityAvailable);
  const quantityTotal = toInteger(input.quantityTotal ?? quantityAvailable, quantityAvailable);

  return {
    product: {
      commerce_id: toInteger(input.commerceId),
      product_category_id: toInteger(input.productCategoryId),
      title: input.title.trim(),
      description: toTrimmedString(input.description) ?? null,
      product_type: input.productType,
      original_price: toNumber(input.originalPrice),
      discounted_price:
        input.discountedPrice === null || input.discountedPrice === undefined
          ? null
          : toNumber(input.discountedPrice),
      quantity_total: quantityTotal,
      quantity_available: quantityAvailable,
      expires_at: input.expiresAt ?? null,
      status: input.status ?? "1",
    },
    commerce_branches: normalizeBranchAsArray(input.branchId),
    package_items: normalizePackageItems(input),
    photos: normalizePhotos(input),
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
 * DELETE /api/v1/products/{id}
 * Elimina producto o pack
 */
export async function deleteProduct(productId: number): Promise<void> {
  await fetchWithErrorHandling<void>(`/api/v1/products/${productId}`, {
    method: "DELETE",
  });
}
