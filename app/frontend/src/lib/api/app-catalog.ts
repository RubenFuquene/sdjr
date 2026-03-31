import { ApiError, fetchWithErrorHandling } from "./client";
import type { NearbyProductsParams, NearbyProductsResponse } from "@/types/app-catalog";

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
