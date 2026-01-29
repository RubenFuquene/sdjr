/**
 * API Module - Barrel Export
 * Centraliza todas las exportaciones de la API para imports limpios
 * 
 * Usage:
 * import { login, getRoles, ApiError } from '@/lib/api/index';
 */

// Core utilities
export { API_URL, ApiError, getAuthHeaders, fetchWithErrorHandling } from "./client";

// Authentication
export { login, logout } from "./auth";
export type { LoginResult } from "./auth";

// Roles (Perfiles)
export { getRoles, createRole, updateRole, updateRoleStatus, deleteRole } from "./roles";

// Commerces (Proveedores)
export { getCommerces, getCommerceById, updateCommerce, deleteCommerce } from "./commerces";

// Users (Usuarios)
export { 
  getUsers, 
  getUserById, 
  createUser, 
  updateUser, 
  updateUserStatus, 
  deleteUser,
  getAdministrators 
} from "./users";
export type { ApiSuccess } from "./users";

// Location (Geolocalización)
export {
  getCountries,
  getCountry,
  getDepartments,
  getDepartment,
  getCities,
  getCity,
  getNeighborhoods,
  getNeighborhood
} from "./location";
