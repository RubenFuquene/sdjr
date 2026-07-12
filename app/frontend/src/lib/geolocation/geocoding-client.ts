/**
 * Cliente de geocoding (directo/inverso) para el flujo de coherencia de
 * ubicación en sucursal. Envuelve la API del proxy backend con una caché en
 * memoria (evita repetir la misma consulta/punto durante la sesión) y
 * degrada a `null` ante cualquier fallo — el geocoding es una asistencia,
 * nunca bloquea el flujo manual (el pin en el mapa sigue siendo válido).
 */

import { geocodeAddress, reverseGeocodeCoordinates, type GeocodeResult } from '@/lib/api/geocode';

const cache = new Map<string, GeocodeResult | null>();

function queryCacheKey(query: string): string {
  return `q:${query.trim().toLowerCase()}`;
}

function coordsCacheKey(lat: number, lng: number): string {
  return `r:${lat.toFixed(5)}:${lng.toFixed(5)}`;
}

/**
 * Geocoding directo: dirección de texto → punto geográfico.
 * Retorna null si no hay resultados o si la solicitud falla (nunca lanza).
 */
export async function geocode(query: string): Promise<GeocodeResult | null> {
  const trimmed = query.trim();
  if (trimmed === '') {
    return null;
  }

  const key = queryCacheKey(trimmed);
  if (cache.has(key)) {
    return cache.get(key) ?? null;
  }

  try {
    const result = await geocodeAddress(trimmed);
    cache.set(key, result);
    return result;
  } catch {
    cache.set(key, null);
    return null;
  }
}

/**
 * Geocoding inverso: punto geográfico → dirección/barrio/ciudad aproximados.
 * Retorna null si no hay resultados o si la solicitud falla (nunca lanza).
 */
export async function reverseGeocodePoint(lat: number, lng: number): Promise<GeocodeResult | null> {
  const key = coordsCacheKey(lat, lng);
  if (cache.has(key)) {
    return cache.get(key) ?? null;
  }

  try {
    const result = await reverseGeocodeCoordinates(lat, lng);
    cache.set(key, result);
    return result;
  } catch {
    cache.set(key, null);
    return null;
  }
}
