/**
 * Location Types - Geolocalización Colombia
 * Basado en Laravel Resources: CountryResource, DepartmentResource, CityResource, NeighborhoodResource
 */

/**
 * Country (País)
 * GET /api/v1/countries
 */
export interface Country {
  id: number;
  code: string;
  name: string;
  status: string; // "A" = Activo, "I" = Inactivo
  created_at: string;
  updated_at: string;
}

/**
 * Department (Departamento)
 * GET /api/v1/departments
 */
export interface Department {
  id: number;
  country_id: number;
  code: string;
  name: string;
  status: string; // "A" = Activo, "I" = Inactivo
  country?: Country; // Incluido si se usa ?with=country
  created_at: string;
  updated_at: string;
}

/**
 * City (Ciudad)
 * GET /api/v1/cities
 */
export interface City {
  id: number;
  department_id: number;
  code: string;
  name: string;
  status: string; // "A" = Activo, "I" = Inactivo
  department?: Department; // Incluido si se usa ?with=department
  created_at: string;
  updated_at: string;
}

/**
 * Neighborhood (Barrio)
 * GET /api/v1/neighborhoods
 */
export interface Neighborhood {
  id: number;
  city_id: number;
  code: string;
  name: string;
  status: string; // "A" = Activo, "I" = Inactivo
  city?: City; // Incluido si se usa ?with=city
  created_at: string;
  updated_at: string;
}

/**
 * Paginated Response (usado por todos los endpoints)
 */
export interface PaginatedResponse<T> {
  success: boolean;
  message: string;
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
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
 * Query Params para filtros
 */
export interface LocationQueryParams {
  name?: string; // Filtro parcial por nombre
  code?: string; // Filtro por código
  status?: "1" | "0" | "all"; // 1=activos, 0=inactivos, all=todos
  per_page?: number; // Items por página (1-100), default 15
}

export interface DepartmentQueryParams extends LocationQueryParams {
  country_id?: number; // Filtro por país
}

export interface CityQueryParams extends LocationQueryParams {
  department_id?: number; // Filtro por departamento
}

export interface NeighborhoodQueryParams extends LocationQueryParams {
  city_id?: number; // Filtro por ciudad
}
