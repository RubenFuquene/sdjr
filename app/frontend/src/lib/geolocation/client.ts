import {
  UserLocation,
  LocationErrorCode,
  GeoLocationError,
  GeoProviderOptions,
} from "./types";
import { webGeoProvider } from "./providers/web-provider";
import { ipGeoProvider } from "./providers/ip-provider";

/**
 * Clave de localStorage para cachear ubicación
 */
const LOCATION_CACHE_KEY = "sdjr_user_location_cache";

/**
 * Tiempo de caché por defecto: 15 minutos
 */
const DEFAULT_CACHE_DURATION_MS = 15 * 60 * 1000;

/**
 * Internal interface para almacenar ubicación en cache con metadata
 */
interface CachedLocation {
  location: UserLocation;
  cachedAt: number; // timestamp
}

/**
 * Lee la ubicación del caché (localStorage)
 * Retorna null si no existe o está expirada
 */
export function readLocationCache(): UserLocation | null {
  try {
    if (typeof window === "undefined" || !window.localStorage) {
      return null;
    }

    const cached = window.localStorage.getItem(LOCATION_CACHE_KEY);
    if (!cached) {
      return null;
    }

    const data: CachedLocation = JSON.parse(cached);
    return data.location;
  } catch (error) {
    // Si hay error parseando, ignorar y retornar null
    console.warn("Error leyendo cache de ubicación:", error);
    return null;
  }
}

/**
 * Guarda la ubicación en caché (localStorage)
 */
export function saveLocationCache(location: UserLocation): void {
  try {
    if (typeof window === "undefined" || !window.localStorage) {
      return;
    }

    const cached: CachedLocation = {
      location,
      cachedAt: Date.now(),
    };

    window.localStorage.setItem(LOCATION_CACHE_KEY, JSON.stringify(cached));
  } catch (error) {
    // Si hay error guardando (ej: quota exceeded), ignorar
    console.warn("Error guardando cache de ubicación:", error);
  }
}

/**
 * Verifica si el caché es válido (no ha expirado)
 */
export function isLocationCacheValid(
  location: UserLocation | null,
  maxAgeDurationMs: number = DEFAULT_CACHE_DURATION_MS
): boolean {
  if (!location) {
    return false;
  }

  try {
    const cached = window.localStorage.getItem(LOCATION_CACHE_KEY);
    if (!cached) {
      return false;
    }

    const data: CachedLocation = JSON.parse(cached);
    const ageMs = Date.now() - data.cachedAt;

    return ageMs < maxAgeDurationMs;
  } catch {
    return false;
  }
}

/**
 * Limpia el caché de ubicación
 */
export function clearLocationCache(): void {
  try {
    if (typeof window !== "undefined" && window.localStorage) {
      window.localStorage.removeItem(LOCATION_CACHE_KEY);
    }
  } catch (error) {
    console.warn("Error limpiando cache de ubicación:", error);
  }
}

/**
 * Estrategia principal de resolución de ubicación
 * 1. Intenta browser geolocation (GPS/WiFi/celda)
 * 2. Si falla o rechaza permiso, fallback a IP geolocation
 * 3. Cachea resultado por 15 min para evitar solicitudes innecesarias
 *
 * No bloquea la experiencia del usuario
 */
export async function resolveUserLocation(
  options?: GeoProviderOptions & { cacheMaxAgeMs?: number }
): Promise<UserLocation> {
  // 1. Verificar si hay ubicación válida en caché
  const cached = readLocationCache();
  const cacheMaxAgeMs = options?.cacheMaxAgeMs ?? DEFAULT_CACHE_DURATION_MS;

  if (cached && isLocationCacheValid(cached, cacheMaxAgeMs)) {
    return cached;
  }

  // 2. Intentar obtener del navegador (GPS)
  try {
    const location = await webGeoProvider.getLocation(options);
    saveLocationCache(location);
    return location;
  } catch (webError) {
    // Log del error de GPS para debugging
    const webErrorTyped = webError as GeoLocationError;
    console.debug(
      "[Geo] GPS falló, intentando fallback IP:",
      webErrorTyped.code
    );
  }

  // 3. Fallback a IP geolocation si GPS falló
  try {
    const location = await ipGeoProvider.getLocation(options);
    saveLocationCache(location);
    return location;
  } catch (ipError) {
    // Si ambos fallan, lanzar el error del fallback IP
    throw ipError;
  }
}

/**
 * Obtiene la ubicación, reinintentando con opciones específicas
 * Útil para UI que permite al usuario reintentar manualmente
 */
export async function retryGetUserLocation(
  options?: GeoProviderOptions & { skipCache?: boolean }
): Promise<UserLocation> {
  // Si skip cache es true, no usar caché
  if (options?.skipCache) {
    clearLocationCache();
  }

  return resolveUserLocation(options);
}
