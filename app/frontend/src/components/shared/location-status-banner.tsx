"use client";

import { useUserLocation } from "@/hooks/use-user-location";
import { AlertCircle, CheckCircle2, MapPin, RotateCcw } from "lucide-react";

/**
 * Banner de estado de ubicación para mostrar en dashboard
 * - Verdana: Ubicación precisa (GPS) - discreto/oculto
 * - Advertencia: Ubicación aproximada por IP - amarillo
 * - Error: No se pudo obtener ubicación - rojo
 *
 * No bloquea navegación, es puramente informativo +CTA de reintento
 */
export function LocationStatusBanner() {
  const { location, state, error, refresh, canRetry } = useUserLocation({
    autoFetch: true,
  });

  // Si está en loading o idle, no mostrar nada
  if (state === "idle" || state === "loading") {
    return null;
  }

  // Estado listo con GPS = éxito discreto
  if (state === "ready" && location?.source === "gps") {
    return (
      <div className="mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
        <div className="flex items-start gap-3">
          <CheckCircle2 className="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600" />
          <div className="flex-1">
            <p className="text-sm font-medium text-green-900">
              Ubicación precisa
            </p>
            <p className="mt-1 text-xs text-green-700">
              {location.city && location.country
                ? `${location.city}, ${location.country}`
                : "Ubicación GPS obtenida correctamente"}
              {location.accuracy && (
                <>
                  {" "}
                  · Precisión: ±{Math.round(location.accuracy)}m
                </>
              )}
            </p>
          </div>
        </div>
      </div>
    );
  }

  // Estado listo pero con fallback IP = advertencia suave
  if (state === "ready" && location?.source === "ip") {
    return (
      <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
        <div className="flex items-start gap-3">
          <MapPin className="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600" />
          <div className="flex-1">
            <p className="text-sm font-medium text-amber-900">
              Ubicación aproximada
            </p>
            <p className="mt-1 text-xs text-amber-700">
              Usamos tu IP para mostrar comercios cercanos. Para una ubicación
              más precisa, activa tu GPS.
            </p>
            {canRetry && (
              <button
                onClick={() => refresh()}
                className="mt-2 inline-flex items-center gap-1 text-xs font-medium text-amber-700 hover:text-amber-900 transition-colors"
              >
                <RotateCcw className="h-3 w-3" />
                Reintentar con ubicación precisa
              </button>
            )}
          </div>
        </div>
      </div>
    );
  }

  // Estado denied = usuario rechazó permiso
  if (state === "denied") {
    return (
      <div className="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
        <div className="flex items-start gap-3">
          <AlertCircle className="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600" />
          <div className="flex-1">
            <p className="text-sm font-medium text-blue-900">
              Permiso de ubicación rechazado
            </p>
            <p className="mt-1 text-xs text-blue-700">
              Puedes cambiar este permiso en la configuración de tu navegador.
            </p>
            {canRetry && (
              <button
                onClick={() => refresh()}
                className="mt-2 inline-flex items-center gap-1 text-xs font-medium text-blue-700 hover:text-blue-900 transition-colors"
              >
                <RotateCcw className="h-3 w-3" />
                Solicitar nuevo permiso
              </button>
            )}
          </div>
        </div>
      </div>
    );
  }

  // Estado error = falló obtener ubicación (GPS e IP)
  if (state === "error" && error) {
    return (
      <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-4">
        <div className="flex items-start gap-3">
          <AlertCircle className="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600" />
          <div className="flex-1">
            <p className="text-sm font-medium text-red-900">
              Error obteniendo ubicación
            </p>
            <p className="mt-1 text-xs text-red-700">
              {error.message || "Intenta nuevamente o verifica tu conexión."}
            </p>
            {canRetry && (
              <button
                onClick={() => refresh()}
                className="mt-2 inline-flex items-center gap-1 text-xs font-medium text-red-700 hover:text-red-900 transition-colors"
              >
                <RotateCcw className="h-3 w-3" />
                Reintentar
              </button>
            )}
          </div>
        </div>
      </div>
    );
  }

  return null;
}
