/**
 * Hook para gestión de permisos con adaptador integrado
 * Fetches permisos de BD y aplica adaptador temporal 3→4 niveles
 */

import { useState, useEffect, useMemo } from 'react';
import { PermissionFromAPI, PermissionAdapted, PermissionTree } from '../types/role-form-types';
import { adaptPermissions } from '../components/admin/adapters/permission-adapter';
import { buildPermissionTree } from '../utils/permission-tree-builder';
import { fetchDummyPermissions } from '../lib/dummy-permissions';

export interface UsePermissionsReturn {
  /** Permisos adaptados a 4 niveles */
  permissions: PermissionAdapted[];
  /** Árbol de permisos 4 niveles para navegación */
  permissionTree: PermissionTree;
  /** Estado de carga */
  loading: boolean;
  /** Error si ocurre */
  error: string | null;
  /** Función para recargar permisos */
  refetch: () => Promise<void>;
}

/**
 * Hook principal para gestión de permisos con adaptador integrado
 */
export function usePermissions(): UsePermissionsReturn {
  const [rawPermissions, setRawPermissions] = useState<PermissionFromAPI[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchPermissions = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // 🚨 TEMPORAL: Usar dummy data
      // TODO: Reemplazar con:
      // const response = await fetch('/api/v1/permissions');
      // const data = await response.json();
      // setRawPermissions(data.data || data);
      
      const data = await fetchDummyPermissions();
      setRawPermissions(data);
      
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Error al cargar permisos');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPermissions();
  }, []);

  // 🔄 Aplicar adaptador temporal (3→4 niveles)
  const adaptedPermissions = useMemo(() => {
    if (rawPermissions.length === 0) return [];
    return adaptPermissions(rawPermissions);
  }, [rawPermissions]);

  // Construir árbol 4 niveles
  const permissionTree = useMemo(() => {
    if (adaptedPermissions.length === 0) return {};
    return buildPermissionTree(adaptedPermissions);
  }, [adaptedPermissions]);

  return { 
    permissions: adaptedPermissions, 
    permissionTree, 
    loading,
    error,
    refetch: fetchPermissions
  };
}