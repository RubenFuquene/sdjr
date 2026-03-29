'use client';

import { useState, useEffect } from 'react';
import { getEstablishmentTypes, type EstablishmentType } from '@/lib/api/establishment-types';

interface UseEstablishmentTypesReturn {
  types: EstablishmentType[];
  loading: boolean;
  error: string | null;
}

/**
 * Hook para cargar tipos de establecimiento desde API
 * Los tipos se cargan una sola vez al montar el componente
 * Usa una pequeña dilación para asegurar ejecución post-hidratación
 */
export function useEstablishmentTypes(): UseEstablishmentTypesReturn {
  const [types, setTypes] = useState<EstablishmentType[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Pequeña dilación para asegurar que se ejecuta en cliente (post-hidratación)
    const timer = setTimeout(async () => {
      try {
        const response = await getEstablishmentTypes(100);
        // Extracción robusta con fallback
        if (response?.data && Array.isArray(response.data)) {
          setTypes(response.data);
          setError(null);
        } else {
          setError('No se pudieron cargar los tipos');
          setTypes([]);
        }
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'Error desconocido';
        console.error('Error fetching establishment types:', errorMessage);
        setError(errorMessage);
        setTypes([]);
      } finally {
        setLoading(false);
      }
    }, 0);

    return () => clearTimeout(timer);
  }, []);

  return {
    types,
    loading,
    error,
  };
}
