"use client";

import { useCallback, useEffect, useState } from "react";
import { MapContainer, TileLayer, Marker, useMap, useMapEvents } from "react-leaflet";
import { X, MapPin } from "lucide-react";
import type L from "leaflet";
import "leaflet/dist/leaflet.css";
import { OPENSTREETMAP_TILES } from "@/lib/maps/tile-configs";
import { createCommerceMarkerIcon } from "@/lib/maps/constants";

// Colombia center — default when no previous coordinates
const COLOMBIA_CENTER: [number, number] = [4.7110, -74.0721];
const COLOMBIA_ZOOM = 6;
const SELECTED_ZOOM = 15;

interface LocationPickerModalProps {
  isOpen: boolean;
  initialLat?: number | null;
  initialLng?: number | null;
  onClose: () => void;
  onSelect: (lat: number, lng: number) => void;
}

interface SelectedCoords {
  lat: number;
  lng: number;
}

/**
 * ClickHandler — inner component that must live inside MapContainer.
 * Listens for map click events and updates the selected coordinates.
 */
function ClickHandler({
  onMapClick,
}: {
  onMapClick: (coords: SelectedCoords) => void;
}) {
  useMapEvents({
    click(event: L.LeafletMouseEvent) {
      onMapClick({ lat: event.latlng.lat, lng: event.latlng.lng });
    },
  });
  return null;
}

function MapResizeHandler() {
  const map = useMap();

  useEffect(() => {
    // Leaflet can compute an incorrect size when mounted inside modals.
    // Recalculate after paint to prevent blank/ghost interactive zones.
    const id = window.setTimeout(() => {
      map.invalidateSize();
    }, 50);

    return () => window.clearTimeout(id);
  }, [map]);

  return null;
}

/**
 * LocationPickerModal
 *
 * Allows the provider to select the exact geographic location of a branch
 * by clicking on an interactive Leaflet map.
 *
 * - If initialLat/initialLng are provided (edit mode), the map centers there
 *   with the pin already placed.
 * - Click anywhere on the map to place/move the pin.
 * - "Confirmar ubicación" is disabled until a pin is placed.
 * - Sits above BranchFormModal (z-[9999] vs z-50).
 *
 * Must be imported via next/dynamic with ssr:false from the parent form.
 */
export function LocationPickerModal({
  isOpen,
  initialLat,
  initialLng,
  onClose,
  onSelect,
}: LocationPickerModalProps) {
  const hasInitialCoords =
    typeof initialLat === "number" && typeof initialLng === "number";

  const [selected, setSelected] = useState<SelectedCoords | null>(
    hasInitialCoords ? { lat: initialLat!, lng: initialLng! } : null
  );

  const center: [number, number] = hasInitialCoords
    ? [initialLat!, initialLng!]
    : COLOMBIA_CENTER;

  const zoom = hasInitialCoords ? SELECTED_ZOOM : COLOMBIA_ZOOM;

  const handleMapClick = useCallback((coords: SelectedCoords) => {
    setSelected(coords);
  }, []);

  const handleConfirm = () => {
    if (!selected) return;
    onSelect(selected.lat, selected.lng);
  };

  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 z-[9999] bg-black/60 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-label="Seleccionar ubicación en mapa"
    >
      <div className="bg-white rounded-[18px] shadow-2xl w-full max-w-3xl flex flex-col overflow-hidden max-h-[90vh]">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-[#E0E0E0] shrink-0">
          <div>
            <h3 className="text-lg font-semibold text-[#1A1A1A]">
              Seleccionar ubicación
            </h3>
            <p className="text-sm text-[#6A6A6A] mt-0.5">
              Haz clic en el mapa para marcar el punto exacto de la sucursal
            </p>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Cerrar selector de ubicación"
            className="text-[#6A6A6A] hover:text-[#1A1A1A] transition-colors"
          >
            <X size={22} />
          </button>
        </div>

        {/* Map */}
        <div className="relative h-[320px] md:h-[420px] shrink-0 overflow-hidden isolate z-0">
          <MapContainer
            center={center}
            zoom={zoom}
            className="w-full h-full overflow-hidden"
            scrollWheelZoom
            dragging
            touchZoom
            doubleClickZoom
            style={{ cursor: "crosshair", height: "100%", minHeight: "320px" }}
          >
            <TileLayer
              url={OPENSTREETMAP_TILES.url}
              attribution={OPENSTREETMAP_TILES.attribution}
              maxZoom={OPENSTREETMAP_TILES.maxZoom}
            />

            <MapResizeHandler />

            <ClickHandler onMapClick={handleMapClick} />

            {selected && (
              <Marker
                position={[selected.lat, selected.lng]}
                icon={createCommerceMarkerIcon(true)}
              />
            )}
          </MapContainer>

          {/* Hint overlay — shown only when no pin yet */}
          {!selected && (
            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 z-[400] pointer-events-none">
              <div className="bg-white/95 backdrop-blur-sm rounded-xl px-4 py-2 shadow-md border border-[#E0E0E0] flex items-center gap-2">
                <MapPin size={15} className="text-[#4B236A] shrink-0" />
                <span className="text-xs text-[#6A6A6A]">
                  Haz clic en el mapa para fijar la ubicación
                </span>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="px-6 py-4 border-t border-[#E0E0E0] shrink-0 flex items-center justify-between gap-4 relative z-10 bg-white">
          {/* Coordinates display */}
          <div className="text-sm text-[#6A6A6A] min-h-[20px]">
            {selected ? (
              <span className="flex items-center gap-1.5 text-[#1A1A1A]">
                <MapPin size={14} className="text-[#4B236A] shrink-0" />
                <span>
                  {selected.lat.toFixed(6)}°,{" "}
                  {selected.lng.toFixed(6)}°
                </span>
              </span>
            ) : (
              <span className="text-[#6A6A6A]">Sin ubicación seleccionada</span>
            )}
          </div>

          {/* Actions */}
          <div className="flex items-center gap-3 shrink-0">
            <button
              type="button"
              onClick={onClose}
              className="rounded-[14px] h-[44px] px-5 border border-[#DDE8BB] text-[#4B236A] hover:bg-[#DDE8BB] transition-colors text-sm"
            >
              Cancelar
            </button>
            <button
              type="button"
              onClick={handleConfirm}
              disabled={!selected}
              className="bg-[#4B236A] hover:bg-[#5D2B7D] text-white rounded-[14px] h-[44px] px-5 shadow-md transition-colors text-sm disabled:opacity-40 disabled:cursor-not-allowed"
            >
              Confirmar ubicación
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
