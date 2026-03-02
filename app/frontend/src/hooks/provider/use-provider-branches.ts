'use client';

import { useCallback, useEffect, useState } from 'react';
import { ApiError, getMyCommerce } from '@/lib/api';
import { getCommerceBranches } from '@/lib/api/branches';
import type { CommerceBranchFromAPI } from '@/lib/api/branches';

interface BranchFilters {
  page?: number;
  perPage?: number;
}

interface UseProviderBranchesReturn {
  branches: CommerceBranchFromAPI[];
  loading: boolean;
  error: string | null;
  hasCommerce: boolean;
  commerceId: number | null;
  currentPage: number;
  lastPage: number;
  totalBranches: number;
  refresh: () => Promise<void>;
  setPage: (page: number) => Promise<void>;
}

/**
 * Hook para obtener sucursales del proveedor autenticado.
 * Flujo: /api/v1/me/commerce -> /api/v1/commerces/{commerce_id}/branches
 */
export function useProviderBranches(): UseProviderBranchesReturn {
  const [branches, setBranches] = useState<CommerceBranchFromAPI[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [hasCommerce, setHasCommerce] = useState(false);
  const [commerceId, setCommerceId] = useState<number | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [totalBranches, setTotalBranches] = useState(0);
  const [filters, setFilters] = useState<BranchFilters>({
    page: 1,
    perPage: 15,
  });

  const fetchBranches = useCallback(async (customFilters?: BranchFilters) => {
    try {
      setLoading(true);
      setError(null);

      const activeFilters = customFilters ?? filters;

      const myCommerceResponse = await getMyCommerce();
      const myCommerce = myCommerceResponse.data;

      if (!myCommerce?.id) {
        setHasCommerce(false);
        setCommerceId(null);
        setBranches([]);
        setCurrentPage(1);
        setLastPage(1);
        setTotalBranches(0);
        return;
      }

      setHasCommerce(true);
      setCommerceId(myCommerce.id);

      const response = await getCommerceBranches(myCommerce.id, {
        page: activeFilters.page,
        perPage: activeFilters.perPage,
      });

      setBranches(response.data);
      setCurrentPage(response.meta.current_page);
      setLastPage(response.meta.last_page);
      setTotalBranches(response.meta.total);
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.status === 404) {
          setHasCommerce(false);
          setCommerceId(null);
          setBranches([]);
          setCurrentPage(1);
          setLastPage(1);
          setTotalBranches(0);
          return;
        }
        setError(err.message);
      } else {
        setError('Error inesperado al cargar sucursales');
      }
      console.error('Error fetching provider branches:', err);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    fetchBranches();
  }, [fetchBranches]);

  const refresh = useCallback(async () => {
    await fetchBranches();
  }, [fetchBranches]);

  const setPage = useCallback(async (page: number) => {
    const newFilters = { ...filters, page };
    setFilters(newFilters);
    await fetchBranches(newFilters);
  }, [fetchBranches, filters]);

  return {
    branches,
    loading,
    error,
    hasCommerce,
    commerceId,
    currentPage,
    lastPage,
    totalBranches,
    refresh,
    setPage,
  };
}
