/**
 * Geocode API - Proxy backend hacia Nominatim (OpenStreetMap)
 * Endpoints: /api/v1/geocode, /api/v1/geocode/reverse
 *
 * Nunca se llama a Nominatim directamente desde el navegador: el backend
 * identifica la app, cachea y limita la tasa de salida. Ver GeocodingService.
 */

import { fetchWithErrorHandling } from "./client";

export interface GeocodeAddress {
  road: string | null;
  house_number: string | null;
  neighborhood: string | null;
  city: string | null;
  state: string | null;
}

export interface GeocodeResult {
  lat: number;
  lng: number;
  display_name: string | null;
  address: GeocodeAddress;
}

interface GeocodeApiResponse {
  status: boolean;
  message: string;
  data: GeocodeResult;
}

/**
 * GET /api/v1/geocode - Geocoding directo (dirección de texto → punto)
 */
export async function geocodeAddress(query: string): Promise<GeocodeResult> {
  const response = await fetchWithErrorHandling<GeocodeApiResponse>(
    `/api/v1/geocode?q=${encodeURIComponent(query)}`
  );
  return response.data;
}

/**
 * GET /api/v1/geocode/reverse - Geocoding inverso (punto → dirección aproximada)
 */
export async function reverseGeocodeCoordinates(lat: number, lng: number): Promise<GeocodeResult> {
  const response = await fetchWithErrorHandling<GeocodeApiResponse>(
    `/api/v1/geocode/reverse?lat=${lat}&lng=${lng}`
  );
  return response.data;
}
