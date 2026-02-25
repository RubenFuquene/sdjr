"use client";

import { createContext, ReactNode } from "react";
import type L from "leaflet";

/**
 * Contexto para compartir la referencia del mapa Leaflet
 * Permite que componentes secundarios controlen el mapa sin prop drilling
 */
export interface MapControlContextType {
  mapRef: React.MutableRefObject<L.Map | null>;
}

export const MapControlContext = createContext<MapControlContextType | null>(
  null
);

/**
 * Provider para MapControlContext
 * Envuelve el árbol de componentes que necesita acceso al mapa
 *
 * @example
 * ```tsx
 * <MapControlProvider mapRef={mapRef}>
 *   <UserLocationMap location={location} />
 *   <SomeChildComponent /> // puede usar useMapControl()
 * </MapControlProvider>
 * ```
 */
export function MapControlProvider({
  mapRef,
  children,
}: {
  mapRef: React.MutableRefObject<L.Map | null>;
  children: ReactNode;
}) {
  return (
    <MapControlContext.Provider value={{ mapRef }}>
      {children}
    </MapControlContext.Provider>
  );
}
