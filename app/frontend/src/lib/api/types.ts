/**
 * Shared API Type Definitions
 * Centralize common response/request types used across all API modules
 */

/**
 * Standard API Success Response
 * @template T - Type of data in the response
 */
export interface ApiSuccess<T> {
  status: boolean;
  message?: string;
  data: T;
}

/**
 * Paginated Response Metadata
 */
export interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

/**
 * Paginated Links
 */
export interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

/**
 * Standard Paginated API Response
 * @template T - Type of items in the data array
 */
export interface PaginatedApiResponse<T> {
  data: T[];
  meta: PaginationMeta;
  links: PaginationLinks;
}
