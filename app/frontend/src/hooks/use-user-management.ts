/**
 * Hook de Gestión de Usuarios - Centralizado
 *
 * Encapsula toda la lógica relacionada con usuarios:
 * - Data fetching desde API /api/v1/users
 * - CRUD operations (create, update, delete, toggle status)
 * - Mapeo de datos API → Frontend types
 * - Gestión de estado (loading, error, refresh, search)
 *
 * Responsabilidad única: Toda la lógica de negocio de usuarios
 *
 * @example
 * const userManagement = useUserManagement();
 *
 * // Data
 * console.log(userManagement.usuarios);
 * console.log(userManagement.loading);
 *
 * // Handlers
 * await userManagement.handleSearch({ search: "juan" });
 * await userManagement.handleToggle(id);
 *
 * // Refresh
 * await userManagement.refresh();
 */

"use client";

import { useState, useEffect, useCallback } from "react";
import {
  getUsers,
  updateUserStatus,
  deleteUser,
  ApiError,
} from "@/lib/api/index";
import type { Usuario } from "@/types/admin";
import { usersFromAPIToUsuarios } from "@/types/user.adapters";

/**
 * Parámetros de búsqueda y filtrado para usuarios
 */
export interface UserFilters {
  page?: number;
  perPage?: number;
  search?: string;  // Busca en name, last_name, email
  role?: string;    // Filtra por rol
  status?: '1' | '0';  // Filtra por estado
}

/**
 * Hook de gestión centralizado de usuarios
 */
export function useUserManagement(perPage: number = 15) {
  // Estado de datos
  const [usuarios, setUsuarios] = useState<Usuario[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Estado de filtros y paginación
  const [filters, setFilters] = useState<UserFilters>({
    page: 1,
    perPage,
  });

  // Estado de paginación (metadata del backend)
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalUsers, setTotalUsers] = useState(0);

  /**
   * Fetch de usuarios desde API
   * GET /api/v1/users?page={page}&per_page={perPage}&search={search}&role={role}&status={status}
   */
  const fetchUsuarios = useCallback(async (customFilters?: UserFilters) => {
    try {
      setLoading(true);
      setError(null);

      const currentFilters = customFilters || filters;
      const response = await getUsers({
        page: currentFilters.page,
        perPage: currentFilters.perPage,
        search: currentFilters.search,
        role: currentFilters.role,
        status: currentFilters.status,
      });

      // Mapear respuesta del backend a tipos de frontend
      const mappedUsuarios = usersFromAPIToUsuarios(response.data);
      setUsuarios(mappedUsuarios);

      // Actualizar metadata de paginación
      setCurrentPage(response.meta.current_page);
      setTotalPages(response.meta.last_page);
      setTotalUsers(response.meta.total);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error inesperado al cargar usuarios");
      }
      console.error("Error fetching usuarios:", err);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  /**
   * Cargar usuarios al montar el componente
   */
  useEffect(() => {
    fetchUsuarios();
  }, [fetchUsuarios]);

  /**
   * Buscar usuarios con filtros
   */
  const handleSearch = useCallback(
    async (searchFilters: Partial<UserFilters>) => {
      try {
        setLoading(true);
        setError(null);

        const newFilters = { ...filters, ...searchFilters, page: 1 };
        setFilters(newFilters);

        const response = await getUsers({
          page: newFilters.page,
          perPage: newFilters.perPage,
          search: newFilters.search,
          role: newFilters.role,
          status: newFilters.status,
        });

        const mappedUsuarios = usersFromAPIToUsuarios(response.data);
        setUsuarios(mappedUsuarios);

        setCurrentPage(response.meta.current_page);
        setTotalPages(response.meta.last_page);
        setTotalUsers(response.meta.total);
      } catch (err) {
        if (err instanceof ApiError) {
          setError(err.message);
        } else {
          setError("Error al buscar usuarios");
        }
        console.error("Error searching usuarios:", err);
      } finally {
        setLoading(false);
      }
    },
    [filters]
  );

  /**
   * Cambia de página
   */
  const handlePageChange = useCallback(async (page: number) => {
    try {
      setLoading(true);
      setError(null);

      const newFilters = { ...filters, page };
      setFilters(newFilters);

      const response = await getUsers({
        page: newFilters.page,
        perPage: newFilters.perPage,
        search: newFilters.search,
        role: newFilters.role,
        status: newFilters.status,
      });

      const mappedUsuarios = usersFromAPIToUsuarios(response.data);
      setUsuarios(mappedUsuarios);

      setCurrentPage(response.meta.current_page);
      setTotalPages(response.meta.last_page);
      setTotalUsers(response.meta.total);
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
   * Activa/Desactiva un usuario (toggle status)
   * PATCH /api/v1/users/{id}/status
   */
  const handleToggle = useCallback(
    async (id: number): Promise<void> => {
      try {
        const currentUser = usuarios.find((u) => u.id === id);
        if (!currentUser) {
          throw new Error("Usuario no encontrado");
        }

        console.log("🚀 Cambiando estado del usuario:", id, !currentUser.activo);

        // Toggle: si está activo (A), cambiar a inactivo (I), y viceversa
        const newStatus = currentUser.activo ? '0' : '1';
        await updateUserStatus(id, newStatus);

        console.log("✅ Estado del usuario actualizado");

        // Refrescar lista
        await fetchUsuarios();
      } catch (error) {
        console.error("❌ Error al cambiar estado del usuario:", error);
        throw error;
      }
    },
    [usuarios, fetchUsuarios]
  );

  /**
   * Elimina un usuario (soft delete)
   * DELETE /api/v1/users/{id}
   */
  const handleDelete = useCallback(
    async (id: number): Promise<void> => {
      try {
        console.log("🚀 Eliminando usuario:", id);

        await deleteUser(id);
        console.log("✅ Usuario eliminado exitosamente");

        // Refrescar lista
        await fetchUsuarios();
      } catch (error) {
        console.error("❌ Error al eliminar usuario:", error);
        throw error;
      }
    },
    [fetchUsuarios]
  );

  /**
   * Reintentar cargar datos en caso de error
   */
  const handleRetry = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      await fetchUsuarios();
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error inesperado al cargar usuarios");
      }
    } finally {
      setLoading(false);
    }
  }, [fetchUsuarios]);

  return {
    // Data
    usuarios,
    loading,
    error,

    // Pagination metadata
    currentPage,
    totalPages,
    totalUsers,

    // Filters & Pagination
    filters,
    setFilters,

    // Handlers
    handleSearch,
    handlePageChange,
    handleToggle,
    handleDelete,
    handleRetry,

    // Utilities
    refresh: fetchUsuarios,
  };
}
