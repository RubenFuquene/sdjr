"use client";

import { useEffect, useRef } from "react";
import { MapContainer, TileLayer, Marker, useMap, useMapEvents } from "react-leaflet";
import { MapPin } from "lucide-react";
import type L from "leaflet";
import "leaflet/dist/leaflet.css";
import { OPENSTREETMAP_TILES } from "@/lib/maps/tile-configs";
import { createCommerceMarkerIcon } from "@/lib/maps/constants";

// Bogotá D.C. — MVP: único departamento/ciudad soportado.
const BOGOTA_CENTER: [number, number] = [4.7110, -74.0721];
const BOGOTA_ZOOM = 12;
const SELECTED_ZOOM = 16;

interface LocationMapFieldProps {
  latitude: number | null;
  longitude: number | null;
  onPinChange: (lat: number, lng: number) => void;
  /** Incrementar para forzar un flyTo (ej. tras geocoding directo exitoso). */
  recenterSignal: number;
}

/**
 * ClickHandler — componente interno que debe vivir dentro de MapContainer.
 * Reporta clicks al padre; no gestiona geocoding (separación de responsabilidades).
 */
function ClickHandler({ onMapClick }: { onMapClick: (lat: number, lng: number) => void }) {
  useMapEvents({
    click(event: L.LeafletMouseEvent) {
      onMapClick(event.latlng.lat, event.latlng.lng);
    },
  });
  return null;
}

function MapResizeHandler() {
  const map = useMap();

  useEffect(() => {
    // Leaflet puede calcular un tamaño incorrecto al montar dentro de layouts
    // con transiciones/reflow. Recalcular tras el paint evita zonas fantasma.
    const id = window.setTimeout(() => {
      map.invalidateSize();
    }, 50);

    return () => window.clearTimeout(id);
  }, [map]);

  return null;
}

/**
 * Vuela el mapa a latitude/longitude cada vez que `recenterSignal` cambia
 * (ej. geocoding directo exitoso). Deliberadamente NO reacciona a cambios de
 * lat/lng por sí solos: un click del usuario en el mapa ya deja el punto
 * donde hizo click, re-centrar ahí produciría un salto brusco innecesario.
 */
function MapRecenter({
  latitude,
  longitude,
  recenterSignal,
}: {
  latitude: number | null;
  longitude: number | null;
  recenterSignal: number;
}) {
  const map = useMap();
  const isFirstRender = useRef(true);

  useEffect(() => {
    if (isFirstRender.current) {
      isFirstRender.current = false;
      return;
    }

    if (latitude !== null && longitude !== null) {
      map.flyTo([latitude, longitude], SELECTED_ZOOM);
    }
    // Reacciona solo a recenterSignal a propósito (ver docstring del componente).
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [recenterSignal]);

  return null;
}

/**
 * Mapa inline (no modal) para el formulario de sucursal.
 *
 * Las coordenadas son la fuente de verdad de la ubicación: este componente
 * solo reporta clicks (onPinChange) y puede ser re-centrado externamente
 * (recenterSignal). La sincronización con dirección/barrio/ciudad la
 * orquesta el formulario padre vía geocoding directo/inverso.
 */
export function LocationMapField({ latitude, longitude, onPinChange, recenterSignal }: LocationMapFieldProps) {
  const hasPin = latitude !== null && longitude !== null;
  const center: [number, number] = hasPin ? [latitude, longitude] : BOGOTA_CENTER;
  const zoom = hasPin ? SELECTED_ZOOM : BOGOTA_ZOOM;

  return (
    <div className="relative h-[280px] md:h-[360px] w-full overflow-hidden isolate z-0 rounded-[14px] border border-[#E0E0E0]">
      <MapContainer
        center={center}
        zoom={zoom}
        className="w-full h-full"
        scrollWheelZoom
        dragging
        touchZoom
        doubleClickZoom
        style={{ cursor: "crosshair", height: "100%", minHeight: "280px" }}
      >
        <TileLayer
          url={OPENSTREETMAP_TILES.url}
          attribution={OPENSTREETMAP_TILES.attribution}
          maxZoom={OPENSTREETMAP_TILES.maxZoom}
        />

        <MapResizeHandler />
        <MapRecenter latitude={latitude} longitude={longitude} recenterSignal={recenterSignal} />
        <ClickHandler onMapClick={onPinChange} />

        {hasPin && <Marker position={[latitude, longitude]} icon={createCommerceMarkerIcon(true)} />}
      </MapContainer>

      {!hasPin && (
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 z-[400] pointer-events-none">
          <div className="bg-white/95 backdrop-blur-sm rounded-xl px-4 py-2 shadow-md border border-[#E0E0E0] flex items-center gap-2">
            <MapPin size={15} className="text-[#4B236A] shrink-0" />
            <span className="text-xs text-[#6A6A6A]">Haz clic en el mapa para fijar la ubicación</span>
          </div>
        </div>
      )}
    </div>
  );
}
