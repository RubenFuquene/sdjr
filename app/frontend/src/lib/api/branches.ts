/**
 * Branches (Sucursales) API Module
 * GET list of branches by commerce
 */

import type { ApiResponse } from "@/types/admin";
import { fetchWithErrorHandling } from "./client";

export interface CommerceBranchHourFromAPI {
  id: number;
  day_of_week: number | string;
  open_time: string;
  close_time: string;
  is_closed?: boolean;
  note?: string | null;
}

export interface CommerceBranchPhotoFromAPI {
  id?: number;
  url?: string;
  file_path?: string;
  mime_type?: string;
}

export interface CommerceBranchFromAPI {
  id: number;
  commerce_id: number;
  name: string;
  address: string;
  department: string | null;
  city: string | null;
  neighborhood: string | null;
  latitude: number | string | null;
  longitude: number | string | null;
  phone: string | null;
  email: string | null;
  is_active: boolean;
  hours?: CommerceBranchHourFromAPI[];
  photos?: CommerceBranchPhotoFromAPI[];
  created_at: string;
  updated_at: string;
}

export interface GetCommerceBranchesParams {
  page?: number;
  perPage?: number;
}

export interface ApiSuccess<T> {
  status: boolean;
  message?: string;
  data: T;
}

export interface CreateCommerceBranchHourInput {
  day_of_week: number;
  open_time: string;
  close_time: string;
  note?: string | null;
}

export interface CreateCommerceBranchPhotoInput {
  file_name: string;
  mime_type: string;
  file_size_bytes: number;
  versioning_enabled?: string;
  metadata?: Record<string, unknown>;
}

export interface CreateCommerceBranchPayload {
  commerce_branch: {
    commerce_id: number;
    department_id: number;
    city_id: number;
    neighborhood_id: number;
    name: string;
    address: string;
    latitude?: number | null;
    longitude?: number | null;
    phone?: string | null;
    email?: string | null;
    status: boolean;
  };
  commerce_branch_hours: CreateCommerceBranchHourInput[];
  commerce_branch_photos: CreateCommerceBranchPhotoInput[];
}

export interface UpdateCommerceBranchPayload {
  department_id?: number;
  city_id?: number;
  neighborhood_id?: number;
  name?: string;
  address?: string;
  latitude?: number | null;
  longitude?: number | null;
  phone?: string | null;
  email?: string | null;
  status?: boolean;
}

function normalizeTimeToHHmm(value: string): string {
  const normalized = value.trim();
  const match = normalized.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);

  if (!match) {
    return normalized;
  }

  const [, rawHours, minutes] = match;
  return `${rawHours.padStart(2, "0")}:${minutes}`;
}

function normalizeBranchHours(
  hours: CommerceBranchHourFromAPI[] | undefined
): CommerceBranchHourFromAPI[] | undefined {
  if (!hours) {
    return hours;
  }

  return hours.map((hour) => ({
    ...hour,
    open_time: normalizeTimeToHHmm(hour.open_time),
    close_time: normalizeTimeToHHmm(hour.close_time),
  }));
}

function normalizeBranch(branch: CommerceBranchFromAPI): CommerceBranchFromAPI {
  return {
    ...branch,
    hours: normalizeBranchHours(branch.hours),
  };
}

/**
 * GET /api/v1/commerces/{commerce_id}/branches
 * Obtiene listado paginado de sucursales para un comercio
 */
export async function getCommerceBranches(
  commerceId: number,
  { page = 1, perPage = 15 }: GetCommerceBranchesParams = {}
): Promise<ApiResponse<CommerceBranchFromAPI>> {
  const params = new URLSearchParams();
  params.set("page", String(page));
  params.set("per_page", String(perPage));

  const response = await fetchWithErrorHandling<ApiResponse<CommerceBranchFromAPI>>(
    `/api/v1/commerces/${commerceId}/branches?${params.toString()}`
  );

  return {
    ...response,
    data: response.data.map(normalizeBranch),
  };
}

/**
 * POST /api/v1/commerce-branches
 * Crea una nueva sucursal
 */
export async function createCommerceBranch(
  payload: CreateCommerceBranchPayload
): Promise<ApiSuccess<CommerceBranchFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<CommerceBranchFromAPI>>(
    `/api/v1/commerce-branches`,
    {
      method: "POST",
      body: JSON.stringify(payload),
    }
  );

  return {
    ...response,
    data: normalizeBranch(response.data),
  };
}

/**
 * PUT /api/v1/commerce-branches/{id}
 * Actualiza una sucursal existente
 */
export async function updateCommerceBranch(
  id: number,
  payload: UpdateCommerceBranchPayload
): Promise<ApiSuccess<CommerceBranchFromAPI>> {
  const response = await fetchWithErrorHandling<ApiSuccess<CommerceBranchFromAPI>>(
    `/api/v1/commerce-branches/${id}`,
    {
      method: "PUT",
      body: JSON.stringify(payload),
    }
  );

  return {
    ...response,
    data: normalizeBranch(response.data),
  };
}
