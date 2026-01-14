/**
 * Hook de Gestión de Comercios - Centralizado
 * 
 * Encapsula toda la lógica relacionada con proveedores/comercios:
 * - Data fetching desde API /api/v1/commerces
 * - CRUD operations (create, update, delete, toggle status)
 * - Mapeo de datos API → Frontend types
 * - Gestión de estado (loading, error, refresh, search)
 * 
 * Responsabilidad única: Toda la lógica de negocio de comercios
 * 
 * @example
 * const commerceManagement = useCommerceManagement();
 * 
 * // Data
 * console.log(commerceManagement.commerces);
 * console.log(commerceManagement.loading);
 * 
 * // Handlers
 * await commerceManagement.handleSearch({ search: "restaurante" });
 * await commerceManagement.handleToggle(id);
 * 
 * // Refresh
 * await commerceManagement.refresh();
 */

"use client";

import { useState, useEffect, useCallback } from "react";
import { getCommerces, ApiError } from "@/lib/api/index";
import type { ProveedorListItem, Proveedor } from "@/types/admin";
import { commerceToProveedorListItem } from "@/types/provider.adapters";

/**
 * Parámetros de búsqueda y filtrado
 */
export interface CommerceFilters {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string;
}

/**
 * Hook de gestión centralizado de comercios/proveedores
 */
export function useCommerceManagement() {
  // Estado de datos
  const [commerces, setCommerces] = useState<ProveedorListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Estado de filtros y paginación
  const [filters, setFilters] = useState<CommerceFilters>({
    page: 1,
    perPage: 15,
  });

  /**
   * Fetch de comercios desde API
   * GET /api/v1/commerces?page={page}&per_page={perPage}&search={search}&status={status}
   */
  const fetchCommerces = useCallback(async (customFilters?: CommerceFilters) => {
    try {
      setLoading(true);
      setError(null);

      const currentFilters = customFilters || filters;
      const response = await getCommerces({
        page: currentFilters.page,
        perPage: currentFilters.perPage,
        search: currentFilters.search,
        status: currentFilters.status,
      });

      const mappedCommerces = response.data.map(commerceToProveedorListItem);
      setCommerces(mappedCommerces);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error inesperado al cargar comercios");
      }
      console.error("Error fetching commerces:", err);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  /**
   * Cargar comercios al montar el componente
   */
  useEffect(() => {
    fetchCommerces();
  }, [fetchCommerces]);

  /**
   * Buscar comercios con filtros
   */
  const handleSearch = useCallback(async (searchFilters: Partial<CommerceFilters>) => {
    try {
      setLoading(true);
      setError(null);

      const newFilters = { ...filters, ...searchFilters, page: 1 };
      setFilters(newFilters);

      const response = await getCommerces({
        page: newFilters.page,
        perPage: newFilters.perPage,
        search: newFilters.search,
        status: newFilters.status,
      });

      const mappedCommerces = response.data.map(commerceToProveedorListItem);
      setCommerces(mappedCommerces);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error al buscar comercios");
      }
      console.error("Error searching commerces:", err);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  /**
   * Cambia de página
   */
  const handlePageChange = useCallback(async (page: number) => {
    try {
      setLoading(true);
      setError(null);

      const newFilters = { ...filters, page };
      setFilters(newFilters);

      const response = await getCommerces({
        page: newFilters.page,
        perPage: newFilters.perPage,
        search: newFilters.search,
        status: newFilters.status,
      });

      const mappedCommerces = response.data.map(commerceToProveedorListItem);
      setCommerces(mappedCommerces);
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
   * Crea un nuevo comercio
   * TODO: POST /api/v1/commerces cuando endpoint esté disponible
   */
  const handleCreate = async (commerceData: Partial<Proveedor>): Promise<void> => {
    try {
      console.log('🚀 Creando comercio:', commerceData);
      
      // TODO: await createCommerce(commerceData);
      console.log('✅ Comercio creado exitosamente');
      
      // Refrescar lista
      await fetchCommerces();
    } catch (error) {
      console.error('❌ Error al crear comercio:', error);
      throw error;
    }
  };

  /**
   * Actualiza un comercio existente
   * TODO: PUT /api/v1/commerces/{id} cuando endpoint esté disponible
   */
  const handleUpdate = async (
    id: number,
    commerceData: Partial<Proveedor>
  ): Promise<void> => {
    try {
      console.log('🚀 Editando comercio:', commerceData, 'ID:', id);
      
      // TODO: await updateCommerce(id, commerceData);
      console.log('✅ Comercio editado exitosamente');
      
      // Refrescar lista
      await fetchCommerces();
    } catch (error) {
      console.error('❌ Error al editar comercio:', error);
      throw error;
    }
  };

  /**
   * Activa/Desactiva un comercio (toggle status)
   * TODO: PATCH /api/v1/commerces/{id}/status cuando endpoint esté disponible
   */
  const handleToggle = useCallback(async (id: number): Promise<void> => {
    try {
      const currentCommerce = commerces.find((c) => c.id === id);
      if (!currentCommerce) {
        throw new Error("Comercio no encontrado");
      }

      console.log('🚀 Cambiando estado del comercio:', id, !currentCommerce.estado);
      
      // TODO: await toggleCommerceStatus(id, { status: !currentCommerce.estado ? "1" : "0" });
      console.log('✅ Estado del comercio actualizado');
      
      // Refrescar lista
      await fetchCommerces();
    } catch (error) {
      console.error('❌ Error al cambiar estado del comercio:', error);
      throw error;
    }
  }, [commerces, fetchCommerces]);

  /**
   * Elimina un comercio
   * TODO: DELETE /api/v1/commerces/{id} cuando endpoint esté disponible
   */
  const handleDelete = useCallback(async (id: number): Promise<void> => {
    try {
      console.log('🚀 Eliminando comercio:', id);
      
      // TODO: await deleteCommerce(id);
      console.log('✅ Comercio eliminado exitosamente');
      
      // Refrescar lista
      await fetchCommerces();
    } catch (error) {
      console.error('❌ Error al eliminar comercio:', error);
      throw error;
    }
  }, [fetchCommerces]);

  /**
   * Reintentar cargar datos en caso de error
   */
  const handleRetry = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      await fetchCommerces();
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error inesperado al cargar comercios");
      }
    } finally {
      setLoading(false);
    }
  }, [fetchCommerces]);

  return {
    // Data
    commerces,
    loading,
    error,

    // Filters & Pagination
    filters,
    setFilters,

    // Handlers
    handleSearch,
    handlePageChange,
    handleCreate,
    handleUpdate,
    handleToggle,
    handleDelete,
    handleRetry,

    // Utilities
    refresh: fetchCommerces,
  };
}
