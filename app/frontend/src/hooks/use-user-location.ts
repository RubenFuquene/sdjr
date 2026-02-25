"use client";

import { useEffect, useState, useCallback } from "react";
import {
  UserLocation,
  UseLocationResult,
  LocationState,
  GeoLocationError,
  LocationErrorCode,
} from "@/lib/geolocation/types";
import {
  retryGetUserLocation,
  clearLocationCache,
} from "@/lib/geolocation/client";

/**
 * Hook para consumir geolocalización del usuario con manejo de estados
 * Requiere "use client" porque maneja estado interactivo
 *
 * Estados:
 * - idle: Inicial, no se ha iniciado la solicitud
 * - loading: Obteniendo ubicación (browser o IP)
 * - ready: Ubicación obtenida con éxito
 * - denied: Usuario rechazó permiso de ubicación
 * - error: Hubo un error obteniendo ubicación (también IP falló)
 *
 * @param autoFetch - Si true (default), obtiene ubicación al montar el hook
 * @param onSuccess - Callback cuando se obtiene ubicación exitosa
 * @param onError - Callback cuando hay error
 *
 * @example
 * const { location, state, error, refresh } = useUserLocation();
 *
 * if (state === "loading") return <Spinner />;
 * if (state === "denied") return <p>Permiso denegado. Ubicación aproximada por IP.</>
 * if (state === "error") return <p>Error: {error?.message}</p>;
 * if (state === "ready" && location) return <p>Ubicación: {location.city}</p>;
 */
export function useUserLocation(
  options?: {
    autoFetch?: boolean;
    onSuccess?: (location: UserLocation) => void;
    onError?: (error: GeoLocationError) => void;
  }
): UseLocationResult {
  const { autoFetch = true, onSuccess, onError } = options || {};

  const [location, setLocation] = useState<UserLocation | null>(null);
  const [state, setState] = useState<LocationState>("idle");
  const [error, setError] = useState<GeoLocationError | null>(null);

  /**
   * Obtiene la ubicación del usuario
   * Mapea errores de permiso denegado a estado 'denied'
   */
  const fetchLocation = useCallback(async (skipCache: boolean = false) => {
    setState("loading");
    setError(null);
    
    try {
      const result = await retryGetUserLocation({ skipCache });
      console.debug("[useUserLocation] Ubicación obtenida:", result);
      setLocation(result);
      setState("ready");
      onSuccess?.(result);
    } catch (err) {
      const geoError = err as GeoLocationError;

      // Si fue permiso denegado, es estado 'denied', no 'error'
      if (geoError.code === LocationErrorCode.PERMISSION_DENIED) {
        setState("denied");
      } else {
        setState("error");
      }

      setError(geoError);
      onError?.(geoError);
    }
  }, [onSuccess, onError]);

  /**
   * Obtiene ubicación al montar el componente (si autoFetch = true)
   */
  useEffect(() => {
    if (!autoFetch) {
      return;
    }

    const timeoutId = window.setTimeout(() => {
      void fetchLocation(false);
    }, 0);

    return () => {
      window.clearTimeout(timeoutId);
    };
  }, [autoFetch, fetchLocation]);

  /**
   * Permite reintentar manualmente (útil para botones de reintento)
   */
  const refresh = useCallback(async () => {
    await fetchLocation(true); // skipCache = true
  }, [fetchLocation]);

  /**
   * Limpia la ubicación y el caché
   */
  const clear = useCallback(() => {
    setLocation(null);
    setState("idle");
    setError(null);
    clearLocationCache();
  }, []);

  /**
   * Indica si el usuario puede reintentar (no está en idle o loading)
   */
  const canRetry = state !== "idle" && state !== "loading";

  return {
    location,
    state,
    error,
    refresh,
    clear,
    canRetry,
  };
}
