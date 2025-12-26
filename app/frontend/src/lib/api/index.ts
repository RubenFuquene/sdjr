/**
 * API Module - Barrel Export
 * Centraliza todas las exportaciones de la API para imports limpios
 * 
 * Usage:
 * import { login, getRoles, ApiError } from '@/lib/api';
 */

// Core utilities
export { API_URL, ApiError, getAuthHeaders, fetchWithErrorHandling } from "./client";

// Authentication
export { login, logout } from "./auth";
export type { LoginResult } from "./auth";

// Roles (Perfiles)
export { getRoles, createRole, updateRole, deleteRole } from "./roles";
