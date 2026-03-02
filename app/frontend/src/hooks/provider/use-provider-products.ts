'use client';

import { useCallback, useEffect, useState } from 'react';
import { ApiError, getMyCommerce, getProductsByCommerce } from '@/lib/api';
import type { ProductFromAPI } from '@/lib/api';

interface UseProviderProductsReturn {
  products: ProductFromAPI[];
  loading: boolean;
  error: string | null;
  hasCommerce: boolean;
  hasProducts: boolean;
  commerceId: number | null;
  refresh: () => Promise<void>;
}

/**
 * Hook para obtener productos del proveedor autenticado.
 * Flujo: /api/v1/me/commerce -> /api/v1/products/commerce/{commerce_id}
 *
 * Nota transitoria:
 * - Mientras backend retorne 404 para "sin productos", ese caso se interpreta como estado vacío.
 * - Contrato objetivo recomendado: 200 con data [] para comercio existente sin productos.
 */
export function useProviderProducts(): UseProviderProductsReturn {
  const [products, setProducts] = useState<ProductFromAPI[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [hasCommerce, setHasCommerce] = useState(false);
  const [hasProducts, setHasProducts] = useState(false);
  const [commerceId, setCommerceId] = useState<number | null>(null);

  const fetchProducts = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);

      const myCommerceResponse = await getMyCommerce();
      const myCommerce = myCommerceResponse.data;

      if (!myCommerce?.id) {
        setHasCommerce(false);
        setHasProducts(false);
        setCommerceId(null);
        setProducts([]);
        return;
      }

      setHasCommerce(true);
      setCommerceId(myCommerce.id);

      const response = await getProductsByCommerce(myCommerce.id);
      const productsData = response.data ?? [];

      setProducts(productsData);
      setHasProducts(productsData.length > 0);
    } catch (err) {
      if (err instanceof ApiError) {
        if (err.status === 404) {
          setProducts([]);
          setHasProducts(false);
          return;
        }

        if (err.status === 401) {
          setError('Tu sesión expiró o no es válida. Inicia sesión para consultar productos.');
          return;
        }

        if (err.status === 403) {
          setError('No tienes permisos para consultar productos del proveedor.');
          return;
        }

        setError(err.message || 'No pudimos cargar los productos.');
      } else {
        setError('Error inesperado al cargar productos.');
      }

      console.error('Error fetching provider products:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const refresh = useCallback(async () => {
    await fetchProducts();
  }, [fetchProducts]);

  return {
    products,
    loading,
    error,
    hasCommerce,
    hasProducts,
    commerceId,
    refresh,
  };
}
