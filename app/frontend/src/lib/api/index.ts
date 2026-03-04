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
  ApiSuccess as BranchApiSuccess,
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
  ApiSuccess as ProductApiSuccess,
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
export type { ApiSuccess } from "./users";

// Administrators (Administradores)
export { getAdministrators } from "./administrators";
export type { ApiSuccess as AdminApiSuccess } from "./administrators";

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
