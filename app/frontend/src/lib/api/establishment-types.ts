import { fetchWithErrorHandling } from './client';
import type { ApiSuccess } from './types';

export interface EstablishmentType {
  id: number;
  name: string;
  code: string;
  status: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

export interface EstablishmentTypesResponse {
  data: EstablishmentType[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

/**
 * Fetch establishment types from API
 * @param perPage Number of types per page (default: 100 for getting all)
 */
export async function getEstablishmentTypes(
  perPage: number = 100
): Promise<ApiSuccess<EstablishmentTypesResponse>> {
  return fetchWithErrorHandling<ApiSuccess<EstablishmentTypesResponse>>(
    `/api/v1/establishment-types?per_page=${perPage}`
  );
}
