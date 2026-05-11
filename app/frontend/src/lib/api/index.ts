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
export { login, logout, requestPasswordReset, resetPassword } from "./auth";
export type { LoginResult, ForgotPasswordResult, ResetPasswordResult } from "./auth";

// Provider Authentication
export { registerProvider } from "./provider-auth";
export type { ProviderRegisterResult } from "./provider-auth";

// App Authentication (customer surface)
export { loginAppUser, registerAppUser } from "./app-auth";
export type { AppLoginResult, AppRegisterResult } from "./app-auth";

// App Catalog (nearby products)
export { getNearbyProducts } from "./app-catalog";
export type { NearbyProductsParams, NearbyProductsResponse } from "@/types/app-catalog";
export {
  mapNearbyProductsToDiscoverCards,
  mapNearbyProductToDiscoverCard,
  mapNearbyProductsToMapPins,
} from "@/types/app-catalog.adapters";
export type { DiscoverNearbyCard, DiscoverMapPin } from "@/types/app-catalog.adapters";

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
  acceptCommerceTerms,
} from "./commerces";
export type { AcceptCommerceTermsPayload } from "./commerces";

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
  getProductById,
  getPackageItemsByProductId,
  getProductCategories,
  createProduct,
  createPackageProduct,
  updateProduct,
  updatePackageProduct,
  deleteProduct,
  mapProductFormToCreatePayload,
  mapProductFormToUpdatePayload,
} from "./products";
export type {
  CreateProductPayload,
  CreateProductPhotoInput,
  GetProductCategoriesParams,
  PackageItemFromAPI,
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
