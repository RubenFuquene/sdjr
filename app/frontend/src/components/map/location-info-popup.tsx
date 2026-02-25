import type { UserLocation } from "@/lib/geolocation/types";

interface LocationInfoPopupProps {
  location: UserLocation;
  onRetry?: () => void;
}

/**
 * Popup informativo de ubicación de usuario.
 *
 * Reglas MVP:
 * - Muestra ciudad/región/país cuando existen en la fuente geográfica.
 * - Muestra precisión solo para GPS.
 * - Muestra CTA de reintento solo cuando la ubicación proviene de IP fallback.
 *
 * Extensión futura:
 * - Este componente puede recibir variantes (user/comercio) para reutilizar layout
 *   de popup manteniendo semántica y accesibilidad.
 */
export function LocationInfoPopup({
  location,
  onRetry,
}: LocationInfoPopupProps) {
  const locationLabel = [location.city, location.region, location.country]
    .filter(Boolean)
    .join(", ");

  const hasIpFallback = location.source === "ip";

  return (
    <div className="space-y-2" role="dialog" aria-label="Información de ubicación">
      <div className="font-semibold text-[#2E2E2E] text-sm">
        {locationLabel || "Ubicación detectada"}
      </div>

      {location.region && (
        <div className="text-[#7A2E9A]">
          <span className="text-xs">Región:</span>
          <div className="text-xs mt-0.5">{location.region}</div>
        </div>
      )}

      {location.country && (
        <div className="text-[#7A2E9A]">
          <span className="text-xs">País:</span>
          <div className="text-xs mt-0.5">{location.country}</div>
        </div>
      )}

      {location.source === "gps" && typeof location.accuracy === "number" && (
        <div className="text-[#7A2E9A]">
          <span className="text-xs">Precisión:</span>
          <div className="text-xs mt-0.5">±{Math.round(location.accuracy)} metros</div>
        </div>
      )}

      <div className="pt-2 border-t border-[#E6E6E6]">
        <span className="inline-block text-xs font-medium px-2 py-1 rounded-lg bg-[#DDE8BB] text-[#5A1E6B]">
          {location.source === "gps"
            ? "🛰️ GPS Preciso"
            : "📍 Ubicación aproximada (IP)"}
        </span>
      </div>

      {hasIpFallback && onRetry && (
        <button
          type="button"
          onClick={onRetry}
          className="w-full mt-2 px-3 py-2 bg-[#5A1E6B] hover:bg-[#7A2E9A] text-white text-xs font-medium rounded-lg transition-colors"
        >
          Reintentar ubicación precisa
        </button>
      )}
    </div>
  );
}
