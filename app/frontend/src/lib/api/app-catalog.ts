import { ApiError, fetchWithErrorHandling } from "./client";
import type {
  BranchDetailResponse,
  NearbyProductsParams,
  NearbyProductsResponse,
  ProductDetailResponse,
} from "@/types/app-catalog";

function ensureValidId(id: number, label: string): void {
  if (!Number.isInteger(id) || id <= 0) {
    throw new ApiError(`${label} inválido.`, 422);
  }
}

function ensureValidCoordinates(latitude: number, longitude: number): void {
  if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
    throw new ApiError("Coordenadas invalidas para buscar productos cercanos.", 422);
  }
}

function buildNearbyProductsQuery(params: NearbyProductsParams): string {
  const query = new URLSearchParams();

  query.set("latitude", String(params.latitude));
  query.set("longitude", String(params.longitude));

  if (params.radius !== undefined) {
    query.set("radius", String(params.radius));
  }

  if (params.categoryId !== undefined) {
    query.set("category_id", String(params.categoryId));
  }

  if (params.maxPrice !== undefined) {
    query.set("max_price", String(params.maxPrice));
  }

  if (params.perPage !== undefined) {
    query.set("per_page", String(params.perPage));
  }

  if (params.page !== undefined) {
    query.set("page", String(params.page));
  }

  return query.toString();
}

/**
 * GET /api/v1/nearby/products
 * Endpoint publico para listar productos cercanos por lat/lng.
 */
export async function getNearbyProducts(
  params: NearbyProductsParams
): Promise<NearbyProductsResponse> {
  ensureValidCoordinates(params.latitude, params.longitude);

  const queryString = buildNearbyProductsQuery(params);

  return fetchWithErrorHandling<NearbyProductsResponse>(
    `/api/v1/nearby/products?${queryString}`,
    {
      method: "GET",
    }
  );
}

/**
 * GET /api/v1/catalog/products/{id}
 * Detalle público de un producto activo. 404 si no existe o está inactivo.
 */
export async function getProductDetail(id: number): Promise<ProductDetailResponse> {
  ensureValidId(id, "Id de producto");

  return fetchWithErrorHandling<ProductDetailResponse>(`/api/v1/catalog/products/${id}`, {
    method: "GET",
  });
}

/**
 * GET /api/v1/catalog/commerce-branches/{id}
 * Detalle público de una sucursal/tienda activa. 404 si no existe o está inactiva.
 */
export async function getBranchDetail(id: number): Promise<BranchDetailResponse> {
  ensureValidId(id, "Id de sucursal");

  return fetchWithErrorHandling<BranchDetailResponse>(`/api/v1/catalog/commerce-branches/${id}`, {
    method: "GET",
  });
}
