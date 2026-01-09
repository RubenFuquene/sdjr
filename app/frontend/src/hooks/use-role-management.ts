/**
 * Hook de Gestión de Roles
 * 
 * Encapsula toda la lógica de negocio de roles:
 * - Data fetching desde API
 * - CRUD operations (create, update)
 * - Adaptación de datos de API a formato UI
 * 
 * Responsabilidad única: Lógica de negocio de roles
 * 
 * @example
 * const roleManagement = useRoleManagement();
 * 
 * // Data
 * console.log(roleManagement.roles);
 * console.log(roleManagement.loading);
 * 
 * // Handlers
 * await roleManagement.handleCreate(roleData);
 * await roleManagement.handleUpdate(roleId, roleData);
 * 
 * // Transformaciones
 * const adapted = roleManagement.adaptProfileToRole(perfil);
 */

import { useRoles } from './use-roles';
import { createRole, updateRole } from '@/lib/api/roles';
import { adaptPermissions } from '@/components/admin/adapters/permission-adapter';
import { CreateRoleRequest } from '@/types/role-form-types';
import { Perfil } from '@/types/admin';

/**
 * Estructura adaptada de rol para el árbol de permisos
 */
export interface AdaptedRole {
  id: number;
  name: string;
  description: string;
  permissions: string[]; // Permisos con estructura de 4 niveles
}

/**
 * Hook de gestión de roles con lógica de negocio encapsulada
 */
export function useRoleManagement() {
  // Data fetching
  const { roles: perfiles, loading, error, refresh } = useRoles();

  /**
   * Adapta un Perfil de API a formato requerido por el árbol de permisos
   * Transforma permisos de 3 niveles → 4 niveles
   */
  const adaptProfileToRole = (perfil: Perfil): AdaptedRole => {
    // Combinar permisos de admin y proveedor
    const allPermissions = [...perfil.permisosAdmin, ...perfil.permisosProveedor];
    
    // Aplicar adaptador para estructura de 4 niveles
    const adapted4Levels = adaptPermissions(allPermissions);
    
    return {
      id: perfil.id,
      name: perfil.nombre,
      description: perfil.descripcion,
      permissions: adapted4Levels.map(p => p.name) // Extraer nombres adaptados
    };
  };

  /**
   * Crea un nuevo rol
   * POST /api/v1/roles
   */
  const handleCreate = async (roleData: CreateRoleRequest): Promise<void> => {
    try {
      console.log('🚀 Creando rol:', roleData);
      
      await createRole({
        name: roleData.name,
        description: roleData.description,
        permissions: roleData.permissions
      });

      console.log('✅ Rol creado exitosamente');
      
      // Refrescar lista de roles
      refresh();
    } catch (error) {
      console.error('❌ Error al crear rol:', error);
      throw error;
    }
  };

  /**
   * Actualiza un rol existente
   * PUT /api/v1/roles/{id}
   */
  const handleUpdate = async (
    id: number, 
    roleData: CreateRoleRequest
  ): Promise<void> => {
    try {
      console.log('🚀 Editando rol:', roleData, 'ID:', id);
      
      await updateRole(id, {
        name: roleData.name,
        description: roleData.description,
        permissions: roleData.permissions
      });

      console.log('✅ Rol editado exitosamente');
      
      // Refrescar lista de roles
      refresh();
    } catch (error) {
      console.error('❌ Error al editar rol:', error);
      throw error;
    }
  };

  return {
    // Data
    roles: perfiles,
    loading,
    error,
    
    // Handlers
    handleCreate,
    handleUpdate,
    
    // Transformaciones
    adaptProfileToRole,
    
    // Utilities
    refresh
  };
}
