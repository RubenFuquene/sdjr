/**
 * Hook para cargar y gestionar roles desde el backend
 * Maneja estados de loading, error y refetch
 */

"use client";

import { useState, useEffect, useCallback } from "react";
import { getRoles, ApiError } from "@/lib/api/index";
import { Perfil, RoleFromAPI } from "@/types/admin";

interface UseRolesReturn {
  roles: Perfil[];
  loading: boolean;
  error: string | null;
  refresh: () => Promise<void>;
}

/**
 * Transforma RoleFromAPI a Perfil (frontend type)
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
    usuarios: role.users_count,
    activo: role.status === "1",
  };
}

/**
 * Hook para obtener roles desde /api/v1/roles
 */
export function useRoles(perPage: number = 15): UseRolesReturn {
  const [roles, setRoles] = useState<Perfil[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchRoles = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);

      const response = await getRoles(perPage);
      const mappedRoles = response.data.map(mapRoleToPerfil);

      setRoles(mappedRoles);
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
  }, [perPage]);

  useEffect(() => {
    fetchRoles();
  }, [fetchRoles]);

  return {
    roles,
    loading,
    error,
    refresh: fetchRoles,
  };
}
