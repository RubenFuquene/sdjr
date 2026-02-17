/**
 * Hook de Gestion de Administradores - Centralizado
 *
 * Encapsula la logica relacionada con administradores:
 * - Data fetching desde API /api/v1/administrators
 * - Mapeo de datos API -> Frontend types
 * - Gestion de estado (loading, error, refresh)
 */

"use client";

import { useCallback, useEffect, useState } from "react";
import { getAdministrators, ApiError } from "@/lib/api/index";
import type { Administrador } from "@/types/admin";
import type { UserFromAPI } from "@/types/user";
import { obtenerRolPrincipal } from "@/types/user.adapters";

const DEFAULT_AREA_LABEL = "Sin area";

function mapUserToAdministrador(user: UserFromAPI): Administrador {
  return {
    id: user.id,
    nombres: user.name,
    apellidos: user.last_name,
    correo: user.email,
    area: DEFAULT_AREA_LABEL,
    perfil: obtenerRolPrincipal(user.roles || []),
    activo: user.status === "1",
  };
}

export function useAdministratorManagement() {
  const [administradores, setAdministradores] = useState<Administrador[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchAdministradores = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);

      const response = await getAdministrators();
      const mapped = response.data.map(mapUserToAdministrador);
      setAdministradores(mapped);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Error inesperado al cargar administradores");
      }
      console.error("Error fetching administradores:", err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchAdministradores();
  }, [fetchAdministradores]);

  const refresh = useCallback(async () => {
    await fetchAdministradores();
  }, [fetchAdministradores]);

  return {
    administradores,
    loading,
    error,
    refresh,
  };
}
