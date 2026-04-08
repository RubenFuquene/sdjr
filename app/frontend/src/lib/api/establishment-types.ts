import { fetchWithErrorHandling } from './client';

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
  status: boolean;
  message?: string;
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
  perPage: number = 100,
  signal?: AbortSignal
): Promise<EstablishmentTypesResponse> {
  return fetchWithErrorHandling<EstablishmentTypesResponse>(
    `/api/v1/establishment-types?per_page=${perPage}`,
    { signal }
  );
}
