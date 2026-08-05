/**
 * Hook de Gestión de Roles - Centralizado
 * 
 * Encapsula toda la lógica relacionada con roles:
 * - Data fetching desde API /api/v1/roles
 * - CRUD operations (create, update)
 * - Mapeo de datos API → Frontend types
 * - Adaptación de datos a formato UI
 * - Gestión de estado (loading, error, refresh)
 * 
 * Responsabilidad única: Toda la lógica de negocio de roles
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
 * // Refresh
 * await roleManagement.refresh();
 */

"use client";

import { useState, useEffect, useCallback } from "react";
import { getRoles, createRole, updateRole, updateRoleStatus, deleteRole, ApiError } from "@/lib/api/index";
import { adaptPermissions } from "@/components/admin/adapters/permission-adapter";
import { CreateRoleRequest } from "@/types/role-form-types";
import { Perfil, RoleFromAPI } from "@/types/admin";
import { useApiErrorHandler } from "./use-api-error-handler";

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
 * Transforma RoleFromAPI a Perfil (frontend type)
 * Mapea permisos por prefijo (admin.*, provider.*)
 */
function mapRoleToPerfil(role: RoleFromAPI): Perfil {
  const permissionsEntries = Object.entries(role.permissions);
  return {
    id: role.id,
    nombre: role.name,
    descripcion: role.description,
    permisosAdmin: permissionsEntries
      .filter(([key]) => key.startsWith("admin."))
      .map(([key, value]) => ({ name: key, description: value })),
    permisosProveedor: permissionsEntries
      .filter(([key]) => key.startsWith("provider."))
      .map(([key, value]) => ({ name: key, description: value })),
    permisosCliente: permissionsEntries
      .filter(([key]) => key.startsWith("customer."))
      .map(([key, value]) => ({ name: key, description: value })),
    usuarios: role.users_count,
    activo: role.status === "1",
  };
}

/**
 * Hook de gestión centralizado de roles
 */
export function useRoleManagement(perPage: number = 15) {
  // Estado de datos
  const [roles, setRoles] = useState<Perfil[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Estado de filtros y paginación
  const [filters, setFilters] = useState({
    page: 1,
    perPage,
  });

  // Estado de paginación (metadata del backend)
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalRoles, setTotalRoles] = useState(0);
  
  // Error handler centralizado
  const handleError = useApiErrorHandler();

  /**
   * Adapta un Perfil de API a formato requerido por el árbol de permisos
   * Transforma permisos de 3 niveles → 4 niveles
   */
  const adaptProfileToRole = (perfil: Perfil): AdaptedRole => {
    // Combinar permisos de admin, proveedor y cliente (SCRUM-9: antes se
    // descartaban los de cliente, quedaban invisibles en el modal).
    const allPermissions = [...perfil.permisosAdmin, ...perfil.permisosProveedor, ...perfil.permisosCliente];
    
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
   * Fetch inicial y refresh de roles desde API
   * GET /api/v1/roles?per_page={perPage}
   */
  const fetchRoles = useCallback(async (customFilters?: { page?: number; perPage?: number }) => {
    try {
      setLoading(true);
      setError(null);

      const currentFilters = customFilters || filters;
      const response = await getRoles({
        page: currentFilters.page,
        perPage: currentFilters.perPage,
      });
      const mappedRoles = response.data.map(mapRoleToPerfil);

      setRoles(mappedRoles);

      setCurrentPage(response.meta.current_page);
      setLastPage(response.meta.last_page);
      setTotalRoles(response.meta.total);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error inesperado al cargar roles");
      }
      console.error("Error fetching roles:", err);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  /**
   * Cargar roles al montar el componente
   */
  useEffect(() => {
    fetchRoles();
  }, [fetchRoles]);

  /**
   * Cambia de página
   */
  const handlePageChange = useCallback(async (page: number) => {
    try {
      setLoading(true);
      setError(null);

      const newFilters = { ...filters, page };
      setFilters(newFilters);

      const response = await getRoles({
        page: newFilters.page,
        perPage: newFilters.perPage,
      });

      const mappedRoles = response.data.map(mapRoleToPerfil);
      setRoles(mappedRoles);

      setCurrentPage(response.meta.current_page);
      setLastPage(response.meta.last_page);
      setTotalRoles(response.meta.total);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error al cambiar página");
      }
      console.error("Error changing page:", err);
    } finally {
      setLoading(false);
    }
  }, [filters]);

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
      await fetchRoles();
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
      await fetchRoles();
    } catch (error) {
      console.error('❌ Error al editar rol:', error);
      throw error;
    }
  };

  /**
   * Cambia el estado de un rol (activo/inactivo)
   * PATCH /api/v1/roles/{id}
   * 
   * Nota: Si el backend retorna 405 (Method Not Allowed), significa que PATCH
   * aún no está implementado. Ver: docs/backend-endpoints-v2.md #1
   */
  const handleToggleRoleStatus = useCallback(async (
    id: number,
    currentStatus: boolean
  ): Promise<void> => {
    try {
      const newStatus = currentStatus ? "0" : "1";
      console.log(`🔄 Cambiando estado del rol ${id} a ${newStatus === "1" ? "activo" : "inactivo"}`);
      
      await updateRoleStatus(id, newStatus);
      
      // Actualizar estado local optimistamente
      setRoles(prev => 
        prev.map(role => 
          role.id === id 
            ? { ...role, activo: newStatus === "1" }
            : role
        )
      );

      console.log('✅ Estado del rol actualizado exitosamente');
    } catch (error) {
      handleError(error);
    }
  }, [handleError]);

  /**
   * Elimina un rol
   * DELETE /api/v1/roles/{id}
   */
  const handleDelete = async (id: number): Promise<void> => {
    try {
      console.log(`🗑️ Eliminando rol ${id}`);
      
      await deleteRole(id);
      
      console.log('✅ Rol eliminado exitosamente');
      
      // Refrescar lista de roles
      await fetchRoles();
    } catch (error) {
      console.error('❌ Error al eliminar rol:', error);
      throw error;
    }
  };

  return {
    // Data
    roles,
    loading,
    error,

    // Pagination metadata
    currentPage,
    lastPage,
    totalRoles,
    perPage: filters.perPage,

    // Filters & Pagination
    filters,
    setFilters,
    
    // Handlers
    handleCreate,
    handleUpdate,
    handleToggleRoleStatus,
    handleDelete,
    handlePageChange,
    
    // Transformaciones
    adaptProfileToRole,
    
    // Utilities
    refresh: fetchRoles
  };
}
