/**
 * Hook: useCommerceApproval
 * 
 * Maneja la aprobación y rechazo de proveedores/comercios
 * Encapsula llamadas a API y manejo de estados
 */

import { useState } from 'react';
import { approveCommerce, rejectCommerce } from '@/lib/api/commerces';
import { CommerceFromAPI } from '@/types/commerces';

interface UseCommerceApprovalState {
  isLoading: boolean;
  error: string | null;
  success: boolean;
}

interface UseCommerceApprovalReturn extends UseCommerceApprovalState {
  approveProvider: (commerceId: number) => Promise<CommerceFromAPI>;
  rejectProvider: (commerceId: number) => Promise<CommerceFromAPI>;
  reset: () => void;
}

/**
 * Hook para manejar aprobación/rechazo de proveedores
 * 
 * Uso:
 * ```tsx
 * const { approveProvider, rejectProvider, isLoading, error } = useCommerceApproval();
 * 
 * const handleApprove = async () => {
 *   try {
 *     const updatedCommerce = await approveProvider(providerId);
 *   } catch (err) {
 *     // Error ya está en el hook
 *   }
 * }
 * ```
 */
export function useCommerceApproval(): UseCommerceApprovalReturn {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const reset = () => {
    setError(null);
    setSuccess(false);
  };

  /**
   * Aprueba un proveedor (is_verified = 1)
   * 
   * @param commerceId - ID del comercio a aprobar
   * @returns Datos actualizados del comercio
   * @throws Error si falla la aprobación
   */
  const approveProvider = async (commerceId: number): Promise<CommerceFromAPI> => {
    setIsLoading(true);
    setError(null);
    setSuccess(false);

    try {
      const response = await approveCommerce(commerceId);
      
      if (!response.data) {
        throw new Error('No se recibieron datos del comercio actualizado');
      }

      setSuccess(true);
      return response.data;
    } catch (err) {
      const mensaje = err instanceof Error ? err.message : 'Error desconocido al aprobar';
      setError(mensaje);
      throw err;
    } finally {
      setIsLoading(false);
    }
  };

  /**
    * Rechaza un proveedor (is_verified = 2)
   * 
   * ⚠️ Nota: El backend no almacena las observaciones de rechazo en este endpoint.
   * Las observaciones deben manejarse en un endpoint separado de PQRS/Comments.
   * 
   * @param commerceId - ID del comercio a rechazar
   * @returns Datos actualizados del comercio
   * @throws Error si falla el rechazo
   */
  const rejectProvider = async (commerceId: number): Promise<CommerceFromAPI> => {
    setIsLoading(true);
    setError(null);
    setSuccess(false);

    try {
      const response = await rejectCommerce(commerceId);
      
      if (!response.data) {
        throw new Error('No se recibieron datos del comercio actualizado');
      }

      setSuccess(true);
      return response.data;
    } catch (err) {
      const mensaje = err instanceof Error ? err.message : 'Error desconocido al rechazar';
      setError(mensaje);
      throw err;
    } finally {
      setIsLoading(false);
    }
  };

  return {
    isLoading,
    error,
    success,
    approveProvider,
    rejectProvider,
    reset,
  };
}
