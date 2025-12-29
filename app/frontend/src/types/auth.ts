/**
 * Authentication and Session Types
 * Shared across the application for type safety
 */

export type Role = "admin" | "provider" | "app";

/**
 * User session data structure
 * Shared between cookies, API responses, and auth guards
 */
export type SessionData = {
  userId: string;
  email: string;
  role: Role;
  name?: string;
  last_name?: string;
  token?: string;
};

/**
 * Laravel API User structure
 */
export type LaravelUser = {
  id: number;
  email: string;
  name?: string;
  last_name?: string;
  phone?: string;
  roles: string[];
  status: string;
  created_at: string;
  updated_at: string;
};

/**
 * Login API response structure from Laravel
 */
export type LoginResponse = {
  message: string;
  data: LaravelUser;
  token: string;
};
