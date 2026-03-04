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
import { getCommerces, getCommerceById, updateCommerce, deleteCommerce, ApiError } from "@/lib/api/index";
import type { ProveedorListItem, Proveedor, CommerceFromAPI } from "@/types/admin";
import { commerceToProveedorListItem, commerceToProveedor } from "@/types/commerces.adapters";

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

  // Estado de paginación (metadata del backend)
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalCommerces, setTotalCommerces] = useState(0);

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

      setCurrentPage(response.meta.current_page);
      setLastPage(response.meta.last_page);
      setTotalCommerces(response.meta.total);
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

      setCurrentPage(response.meta.current_page);
      setLastPage(response.meta.last_page);
      setTotalCommerces(response.meta.total);
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

      setCurrentPage(response.meta.current_page);
      setLastPage(response.meta.last_page);
      setTotalCommerces(response.meta.total);
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
   * PUT /api/v1/commerces/{id}
   */
  const handleUpdate = async (
    id: number,
    commerceData: Partial<Proveedor>
  ): Promise<void> => {
    try {
      console.log('🚀 Editando comercio:', commerceData, 'ID:', id);
      
      await updateCommerce(id, commerceData);
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
   * DELETE /api/v1/commerces/{id}
   */
  const handleDelete = useCallback(async (id: number): Promise<void> => {
    try {
      console.log('🚀 Eliminando comercio:', id);
      
      await deleteCommerce(id);
      console.log('✅ Comercio eliminado exitosamente');
      
      // Refrescar lista
      await fetchCommerces();
    } catch (error) {
      console.error('❌ Error al eliminar comercio:', error);
      throw error;
    }
  }, [fetchCommerces]);

  /**
   * Obtiene el detalle completo de un comercio por ID
   * GET /api/v1/commerces/{id}
   */
  const fetchCommerceById = useCallback(async (id: number): Promise<Proveedor> => {
    try {
      setError(null);

      const response = await getCommerceById(id);
      const commerce: CommerceFromAPI = response.data;

      return commerceToProveedor(commerce);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error al cargar detalle del comercio");
      }
      console.error("Error fetching commerce by id:", err);
      throw err;
    }
  }, []);

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

    // Pagination metadata
    currentPage,
    lastPage,
    totalCommerces,
    perPage: filters.perPage ?? 15,

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
    fetchCommerceById,
    handleRetry,

    // Utilities
    refresh: fetchCommerces,
  };
}
