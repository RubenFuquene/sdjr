"use client";

import { createContext, createElement, type ReactNode } from "react";
import type L from "leaflet";

export interface MapControlContextType {
	mapRef: React.MutableRefObject<L.Map | null>;
}

export const MapControlContext = createContext<MapControlContextType | null>(
	null
);

type MapControlProviderProps = {
	mapRef: React.MutableRefObject<L.Map | null>;
	children: ReactNode;
};

export function MapControlProvider({
	mapRef,
	children,
}: MapControlProviderProps) {
	return createElement(
		MapControlContext.Provider,
		{ value: { mapRef } },
		children
	);
}


