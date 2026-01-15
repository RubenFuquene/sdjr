/**
 * Location API - Geolocalización Colombia
 * Endpoints: /api/v1/countries, /api/v1/departments, /api/v1/cities, /api/v1/neighborhoods
 */

import { fetchWithErrorHandling } from "./client";
import type {
  Country,
  Department,
  City,
  Neighborhood,
  PaginatedResponse,
  LocationQueryParams,
  DepartmentQueryParams,
  CityQueryParams,
  NeighborhoodQueryParams,
} from "@/types/location";

/**
 * Build query string from params
 */
function buildQueryString(params: Record<string, string | number | undefined> | LocationQueryParams | DepartmentQueryParams | CityQueryParams | NeighborhoodQueryParams): string {
  const filtered = Object.entries(params)
    .filter(([, value]) => value !== undefined && value !== "")
    .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`)
    .join("&");
  
  return filtered ? `?${filtered}` : "";
}

// ============================================
// Countries
// ============================================

/**
 * GET /api/v1/countries - Lista de países
 */
export async function getCountries(
  params: LocationQueryParams = {}
): Promise<PaginatedResponse<Country>> {
  const queryString = buildQueryString(params);
  return fetchWithErrorHandling<PaginatedResponse<Country>>(
    `/api/v1/countries${queryString}`
  );
}

/**
 * GET /api/v1/countries/:id - Obtener un país por ID
 */
export async function getCountry(id: number): Promise<{ success: boolean; data: Country }> {
  return fetchWithErrorHandling<{ success: boolean; data: Country }>(
    `/api/v1/countries/${id}`
  );
}

// ============================================
// Departments
// ============================================

/**
 * GET /api/v1/departments - Lista de departamentos
 */
export async function getDepartments(
  params: DepartmentQueryParams = {}
): Promise<PaginatedResponse<Department>> {
  const queryString = buildQueryString(params);
  return fetchWithErrorHandling<PaginatedResponse<Department>>(
    `/api/v1/departments${queryString}`
  );
}

/**
 * GET /api/v1/departments/:id - Obtener un departamento por ID
 */
export async function getDepartment(id: number): Promise<{ success: boolean; data: Department }> {
  return fetchWithErrorHandling<{ success: boolean; data: Department }>(
    `/api/v1/departments/${id}`
  );
}

// ============================================
// Cities
// ============================================

/**
 * GET /api/v1/cities - Lista de ciudades
 */
export async function getCities(
  params: CityQueryParams = {}
): Promise<PaginatedResponse<City>> {
  const queryString = buildQueryString(params);
  return fetchWithErrorHandling<PaginatedResponse<City>>(
    `/api/v1/cities${queryString}`
  );
}

/**
 * GET /api/v1/cities/:id - Obtener una ciudad por ID
 */
export async function getCity(id: number): Promise<{ success: boolean; data: City }> {
  return fetchWithErrorHandling<{ success: boolean; data: City }>(
    `/api/v1/cities/${id}`
  );
}

// ============================================
// Neighborhoods
// ============================================

/**
 * GET /api/v1/neighborhoods - Lista de barrios
 */
export async function getNeighborhoods(
  params: NeighborhoodQueryParams = {}
): Promise<PaginatedResponse<Neighborhood>> {
  const queryString = buildQueryString(params);
  return fetchWithErrorHandling<PaginatedResponse<Neighborhood>>(
    `/api/v1/neighborhoods${queryString}`
  );
}

/**
 * GET /api/v1/neighborhoods/:id - Obtener un barrio por ID
 */
export async function getNeighborhood(id: number): Promise<{ success: boolean; data: Neighborhood }> {
  return fetchWithErrorHandling<{ success: boolean; data: Neighborhood }>(
    `/api/v1/neighborhoods/${id}`
  );
}
