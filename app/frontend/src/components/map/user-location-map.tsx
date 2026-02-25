"use client";

import { useEffect, useRef } from "react";
import { MapContainer, TileLayer, Marker, Popup, useMap } from "react-leaflet";
import type L from "leaflet";
import "leaflet/dist/leaflet.css";
import "./leaflet-custom.css";

import type { UserLocation } from "@/lib/geolocation/types";
import { OPENSTREETMAP_TILES } from "@/lib/maps/tile-configs";
import {
  calculateZoomLevel,
  getUserMarkerIcon,
} from "@/lib/maps/map-utils";
import { MapControlProvider } from "@/lib/maps/map-control-context";
import { LocationInfoPopup } from "@/components/map/location-info-popup";
import { trackEvent } from "@/lib/telemetry/track-event";

interface UserLocationMapProps {
  location: UserLocation | null;
  isLoading?: boolean;
  onRefresh?: () => void;
}

/**
 * MapUpdater Component
 * Helper component that updates map center and zoom when location changes.
 * Necessary because MapContainer is static after initial render.
 */
function MapUpdater({ location }: { location: UserLocation | null }) {
  const map = useMap();

  useEffect(() => {
    if (!location) return;

    const zoom = calculateZoomLevel(location.source);
    map.setView([location.lat, location.lng], zoom, {
      animate: true,
      duration: 0.5,
    });
  }, [location, map]);

  return null;
}

/**
 * UserLocationMap Component
 *
 * Displays the user's current location on an interactive map using Leaflet.
 * - Shows marker in blue for GPS, yellow for IP fallback
 * - Click marker to see location details (city, coordinates, accuracy)
 * - Automatically centers and zooms based on location source
 * - Provides MapControlContext for child components to control the map
 *
 * Extensión futura (Fase 2):
 * - Agregar marcadores de comercios usando un array de puntos + <Marker /> por comercio.
 * - Mantener este componente como contenedor del mapa y delegar popup/marker de comercio
 *   a componentes dedicados para evitar crecimiento de complejidad.
 *
 * Referencias oficiales react-leaflet:
 * - MapContainer: https://react-leaflet.js.org/docs/api-map/
 * - Marker / Popup: https://react-leaflet.js.org/docs/api-components/
 *
 * @param location - UserLocation object with latitude/longitude/source
 * @param isLoading - Optional loading state
 * @param onRefresh - Optional callback to refresh location
 */
export function UserLocationMap({
  location,
  isLoading = false,
  onRefresh,
}: UserLocationMapProps) {
  const mapRef = useRef<L.Map | null>(null);
  const hasTrackedMountedRef = useRef(false);

  // Default center: Bogotá, Colombia (app baseline)
  const defaultCenter: [number, number] = [4.7110, -74.0721];
  const defaultZoom = 12;

  // If we have location, use it; otherwise use default
  const center: [number, number] = location
    ? [location.lat, location.lng]
    : defaultCenter;

  const zoom = location ? calculateZoomLevel(location.source) : defaultZoom;

  useEffect(() => {
    if (!location || hasTrackedMountedRef.current) return;

    trackEvent("map_mounted", {
      source: location.source,
      lat: location.lat,
      lng: location.lng,
      zoom,
      has_accuracy: typeof location.accuracy === "number",
    });

    hasTrackedMountedRef.current = true;
  }, [location, zoom]);

  return (
    <div className="relative w-full h-full overflow-hidden bg-[#F5F5F5]">
      {/* Loading overlay */}
      {isLoading && (
        <div className="absolute inset-0 bg-white/90 backdrop-blur-sm flex items-center justify-center z-[999]">
          <div className="bg-white rounded-xl px-6 py-3 shadow-lg border border-[#E6E6E6]">
            <p className="text-sm text-[#5A1E6B]">Cargando mapa...</p>
          </div>
        </div>
      )}

      <MapControlProvider mapRef={mapRef}>
        <MapContainer
          ref={mapRef}
          center={center}
          zoom={zoom}
          className="w-full h-full z-0 leaflet-container-custom"
          dragging={true}
          touchZoom={true}
          doubleClickZoom={true}
          scrollWheelZoom={true}
          keyboard={true}
          zoomControl={false} // Ocultar controles default, agregar custom
        >
          {/* OpenStreetMap tiles */}
          <TileLayer
            url={OPENSTREETMAP_TILES.url}
            attribution={OPENSTREETMAP_TILES.attribution}
            maxZoom={OPENSTREETMAP_TILES.maxZoom}
          />

          {/* User location marker and popup */}
          {location && (
            <>
              <Marker
                position={[location.lat, location.lng]}
                icon={getUserMarkerIcon(location.source)}
                title={`Tu ubicación (${location.source})`}
                eventHandlers={{
                  click: () => {
                    trackEvent("map_marker_clicked", {
                      marker_type: "user_location",
                      source: location.source,
                      lat: location.lat,
                      lng: location.lng,
                    });
                  },
                }}
              >
                <Popup
                  maxWidth={240}
                  className="location-popup-custom"
                  autoClose={false}
                  closeButton={true}
                  closeOnEscapeKey={true}
                  closeOnClick={true}
                  eventHandlers={{
                    add: () => {
                      trackEvent("map_popup_opened", {
                        popup_type: "user_location",
                        source: location.source,
                      });
                    },
                  }}
                >
                  <LocationInfoPopup location={location} onRetry={onRefresh} />
                </Popup>
              </Marker>

              {/* Map updater to re-center when location changes */}
              <MapUpdater location={location} />
            </>
          )}
        </MapContainer>

        {/* Custom Zoom Controls - Estilo Figma */}
        <div className="absolute bottom-4 right-4 z-[400] flex flex-col gap-2">
          <button
            onClick={() => {
              if (mapRef.current) {
                mapRef.current.zoomIn();
              }
            }}
            className="w-10 h-10 bg-white hover:bg-[#DDE8BB] rounded-lg shadow-md flex items-center justify-center transition-colors border border-[#E6E6E6]"
            aria-label="Acercar"
          >
            <span className="text-[#5A1E6B] text-xl font-semibold">+</span>
          </button>
          <button
            onClick={() => {
              if (mapRef.current) {
                mapRef.current.zoomOut();
              }
            }}
            className="w-10 h-10 bg-white hover:bg-[#DDE8BB] rounded-lg shadow-md flex items-center justify-center transition-colors border border-[#E6E6E6]"
            aria-label="Alejar"
          >
            <span className="text-[#5A1E6B] text-xl font-semibold">−</span>
          </button>
        </div>

        {/* Current Location Indicator - Mobile */}
        <div className="absolute bottom-4 left-4 z-[400] bg-white/95 backdrop-blur-sm rounded-lg px-3 py-2 shadow-md border border-[#E6E6E6] flex items-center gap-2">
          <div className="w-2 h-2 bg-[#5A1E6B] rounded-full animate-pulse"></div>
          <span className="text-xs text-[#7A2E9A]">
            {location?.source === "gps" ? "GPS Activo" : "Ubicación aproximada"}
          </span>
        </div>

        {/* No location state message */}
        {!location && !isLoading && (
          <div className="absolute inset-0 flex items-center justify-center z-[900] bg-[#F5F5F5]">
            <div className="bg-white rounded-2xl px-6 py-6 shadow-lg text-center max-w-xs border border-[#E6E6E6]">
              <div className="w-16 h-16 bg-[#DDE8BB] rounded-full flex items-center justify-center mx-auto mb-4">
                <span className="text-3xl">📍</span>
              </div>
              <p className="text-[#2E2E2E] font-medium mb-2">
                No hay ubicación disponible
              </p>
              <p className="text-[#7A2E9A] text-sm mb-4">
                Activa la ubicación para ver comercios cerca de ti
              </p>
              {onRefresh && (
                <button
                  onClick={onRefresh}
                  className="px-6 py-3 bg-[#5A1E6B] hover:bg-[#7A2E9A] text-white text-sm font-medium rounded-xl transition-colors shadow-sm"
                >
                  Obtener ubicación
                </button>
              )}
            </div>
          </div>
        )}
      </MapControlProvider>
    </div>
  );
}

export default UserLocationMap;
