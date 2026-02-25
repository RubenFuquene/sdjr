import { useContext, useCallback } from "react";
import type L from "leaflet";
import { MapControlContext } from "@/lib/maps/map-control-context";

/**
 * Hook para controlar una instancia de mapa Leaflet
 *
 * Expone métodos para recentrar, cambiar zoom, establecer límites, etc.
 * Debe ser usado dentro de un árbol que tenga MapControlProvider.
 *
 * @example
 * ```tsx
 * const map = useMapControl();
 * map.recenter([4.7110, -74.0721]); // Recentrar
 * map.setZoom(15); // Cambiar zoom
 * map.fitBounds(bounds); // Ajustar view a límites
 * ```
 *
 * @returns {UseMapControlResult} API de control del mapa
 * @throws {Error} Si se llama fuera de MapControlProvider
 */
export function useMapControl() {
  const context = useContext(MapControlContext);

  if (!context) {
    throw new Error(
      "useMapControl debe ser llamado dentro de <MapControlProvider>"
    );
  }

  const { mapRef } = context;

  /**
   * Obtener el centro actual del mapa
   */
  const getCenter = useCallback((): [number, number] | null => {
    if (!mapRef.current) return null;
    const center = mapRef.current.getCenter();
    return [center.lat, center.lng];
  }, [mapRef]);

  /**
   * Obtener el zoom actual del mapa
   */
  const getZoom = useCallback((): number | null => {
    if (!mapRef.current) return null;
    return mapRef.current.getZoom();
  }, [mapRef]);

  /**
   * Recentrar el mapa en una ubicación específica
   * @param lat - Latitud
   * @param lng - Longitud
   * @param zoom - Zoom level opcional
   * @param animate - Animar el movimiento (default: true)
   */
  const recenter = useCallback(
    (
      lat: number,
      lng: number,
      zoom?: number,
      animate: boolean = true
    ) => {
      if (!mapRef.current) return;

      const options: L.ZoomPanOptions = {
        animate,
        duration: animate ? 0.5 : 0,
      };

      if (zoom !== undefined) {
        mapRef.current.setView([lat, lng], zoom, options);
      } else {
        mapRef.current.panTo([lat, lng], options);
      }
    },
    [mapRef]
  );

  /**
   * Establecer el zoom del mapa
   * @param zoom - Nivel de zoom (0-19)
   * @param animate - Animar el cambio (default: true)
   */
  const setZoom = useCallback(
    (zoom: number, animate: boolean = true) => {
      if (!mapRef.current) return;

      if (animate) {
        mapRef.current.flyTo(mapRef.current.getCenter(), zoom, {
          duration: 0.5,
        });
      } else {
        mapRef.current.setZoom(zoom);
      }
    },
    [mapRef]
  );

  /**
   * Establecer límites (bounds) para que el mapa se ajuste a ellos
   * @param bounds - Array de [lat1, lng1, lat2, lng2] o L.LatLngBounds
   * @param padding - Padding en píxeles alrededor de los límites (default: 50)
   */
  const fitBounds = useCallback(
    (
      bounds: L.LatLngBoundsExpression,
      padding: number = 50
    ) => {
      if (!mapRef.current) return;

      try {
        mapRef.current.fitBounds(bounds, {
          padding: [padding, padding],
          animate: true,
          duration: 0.5,
        });
      } catch (error) {
        console.warn("Error al establecer bounds:", error);
      }
    },
    [mapRef]
  );

  /**
   * Resetear el mapa a la vista inicial
   * @param centerLat - Latitud inicial (default: 4.7110 Bogotá)
   * @param centerLng - Longitud inicial (default: -74.0721 Bogotá)
   * @param zoom - Zoom inicial (default: 12)
   */
  const reset = useCallback(
    (
      centerLat: number = 4.7110,
      centerLng: number = -74.0721,
      zoom: number = 12
    ) => {
      if (!mapRef.current) return;
      mapRef.current.setView([centerLat, centerLng], zoom, {
        animate: true,
        duration: 0.5,
      });
    },
    [mapRef]
  );

  /**
   * Invalidar el tamaño del mapa (útil después de cambios de DOM)
   */
  const invalidateSize = useCallback(() => {
    if (!mapRef.current) return;
    mapRef.current.invalidateSize();
  }, [mapRef]);

  /**
   * Activar/desactivar interacción del mapa
   */
  const setInteractive = useCallback((enabled: boolean) => {
    if (!mapRef.current) return;

    if (enabled) {
      mapRef.current.dragging?.enable();
      mapRef.current.touchZoom?.enable();
      mapRef.current.doubleClickZoom?.enable();
      mapRef.current.scrollWheelZoom?.enable();
      mapRef.current.keyboard?.enable();
    } else {
      mapRef.current.dragging?.disable();
      mapRef.current.touchZoom?.disable();
      mapRef.current.doubleClickZoom?.disable();
      mapRef.current.scrollWheelZoom?.disable();
      mapRef.current.keyboard?.disable();
    }
  }, [mapRef]);

  /**
   * Obtener la instancia de Leaflet (para operaciones avanzadas)
   * ADVERTENCIA: Usar solo cuando no haya otra forma. Rompe abstracción.
   */
  const getMap = useCallback(() => mapRef.current, [mapRef]);

  return {
    getCenter,
    getZoom,
    recenter,
    setZoom,
    fitBounds,
    reset,
    invalidateSize,
    setInteractive,
    getMap,
  };
}

export type UseMapControl = ReturnType<typeof useMapControl>;
