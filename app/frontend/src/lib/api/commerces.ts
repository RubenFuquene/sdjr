/**
 * Commerces (Proveedores) API Module
 * GET list of commerces
 */

import { ApiResponse, CommerceFromAPI } from "@/types/admin";
import { fetchWithErrorHandling } from "./client";

export interface GetCommercesParams {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string; // optional: activo/inactivo o 0/1 según backend
}

/**
 * GET /api/v1/commerces
 * Obtiene listado paginado de comercios/proveedores
 */
export async function getCommerces({
  page = 1,
  perPage = 15,
  search,
  status,
}: GetCommercesParams = {}): Promise<ApiResponse<CommerceFromAPI>> {
  const params = new URLSearchParams();
  params.set("page", String(page));
  params.set("per_page", String(perPage));
  if (search) params.set("search", search);
  if (status) params.set("status", status);

  return fetchWithErrorHandling<ApiResponse<CommerceFromAPI>>(
    `/api/v1/commerces?${params.toString()}`
  );
}
