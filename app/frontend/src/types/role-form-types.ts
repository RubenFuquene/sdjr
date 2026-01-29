/**
 * Tipos para el formulario de creación/edición de roles
 * Arquitectura con Adapter Pattern para migración 3→4 niveles
 */

// ============================================
// Backend Types (3 Niveles - Actual)
// ============================================

/**
 * Permiso tal como viene del endpoint /api/v1/permissions (3 niveles)
 */
export interface PermissionFromAPI {
  name: string;        // "admin.roles.create"
  description: string; // "Create roles"
}

/**
 * Request para crear rol en backend
 */
export interface CreateRoleRequest {
  name: string;
  description: string;
  permissions: string[]; // Array de permission names (3 niveles)
}

// ============================================
// Adapted Types (4 Niveles - Frontend)
// ============================================

/**
 * Permiso adaptado a estructura 4 niveles (temporal)
 */
export interface PermissionAdapted {
  name: string;        // "admin.profiles.roles.create"
  description: string; // "Create roles"
  module: string;      // "admin"
  sidebar: string;     // "profiles" (grupo sidebar - auto-mapeo)
  entity: string;      // "roles"
  action: string;      // "create"
}

/**
 * Nodo del árbol de permisos 4 niveles
 */
export interface PermissionTreeNode {
  name: string;
  level: 'module' | 'sidebar' | 'entity' | 'action';
  children?: Record<string, PermissionTreeNode>;
  permissions?: PermissionAdapted[];
}

/**
 * Árbol completo de permisos 4 niveles
 */
export type PermissionTree = Record<string, PermissionTreeNode>;

/**
 * Estado de navegación en el árbol de permisos
 */
export interface PermissionNavigationState {
  currentPath: string[];  // ["admin", "profiles", "roles"]
  selectedPermissions: Set<string>; // Set de permission names originales
  permissionTree: PermissionTree;
}

// ============================================
// Form Types
// ============================================

/**
 * Datos del formulario de rol
 */
export interface RoleFormData {
  name: string;
  description: string;
  selectedPermissions: string[]; // Permission names originales (3 niveles)
}

/**
 * Props del modal principal
 */
export interface RoleCreationModalProps {
  isOpen: boolean;
  mode: 'create' | 'edit' | 'view';
  role?: {
    id: number;
    name: string;
    description: string;
    permissions: string[];
  };
  onClose: () => void;
  onSave: (roleData: CreateRoleRequest) => Promise<void>;
}