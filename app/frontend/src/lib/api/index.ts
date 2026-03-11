/**
 * API Module - Barrel Export
 * Centraliza todas las exportaciones de la API para imports limpios
 * 
 * Usage:
 * import { login, getRoles, ApiError } from '@/lib/api/index';
 */

// ============================================
// Shared Types & Utilities
// ============================================
export type { ApiSuccess, PaginatedApiResponse, PaginationMeta, PaginationLinks } from "./types";

// Core utilities
export { API_URL, ApiError, getAuthHeaders, fetchWithErrorHandling } from "./client";

// Authentication
export { login, register, logout } from "./auth";
export type { LoginResult, RegisterResult } from "./auth";

// Roles (Perfiles)
export { getRoles, createRole, updateRole, updateRoleStatus, deleteRole } from "./roles";

// Commerces (Proveedores)
export {
  getCommerces,
  getCommerceById,
  getMyCommerce,
  createCommerce,
  createCommerceBasic,
  updateCommerce,
  deleteCommerce,
} from "./commerces";

// Branches (Sucursales)
export { getCommerceBranches, createCommerceBranch, updateCommerceBranch } from "./branches";
export type {
  CommerceBranchFromAPI,
  CommerceBranchHourFromAPI,
  CommerceBranchPhotoFromAPI,
  CreateCommerceBranchHourInput,
  CreateCommerceBranchPhotoInput,
  CreateCommerceBranchPayload,
  GetCommerceBranchesParams,
  UpdateCommerceBranchPayload,
} from "./branches";

// Products (Productos)
export {
  getProductsByCommerce,
  getProductCategories,
  createProduct,
  updateProduct,
  deleteProduct,
  mapProductFormToCreatePayload,
  mapProductFormToUpdatePayload,
} from "./products";
export type {
  CreateProductPayload,
  CreateProductPhotoInput,
  GetProductCategoriesParams,
  ProductCategoryFromAPI,
  ProductFormInput,
  ProductFromAPI,
  ProductPhotoFromAPI,
  ProductType,
  UpdateProductPayload,
} from "./products";

// Documents (Carga de archivos)
export {
  createPresignedDocument,
  confirmDocumentUpload,
  deleteDocumentUpload,
} from "./documents";
export type {
  DocumentType,
  PresignedDocumentRequest,
  PresignedDocumentResponse,
  ConfirmDocumentRequest,
  DocumentUploadResource,
} from "./documents";

// Users (Usuarios)
export {
  getUsers,
  getUserById,
  createUser,
  updateUser,
  updateUserStatus,
  deleteUser,
} from "./users";

// Administrators (Administradores)  
export { getAdministrators } from "./administrators";

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

// Establishment Types (Tipos de Establecimiento)
export { getEstablishmentTypes } from "./establishment-types";
export type { EstablishmentType, EstablishmentTypesResponse } from "./establishment-types";
