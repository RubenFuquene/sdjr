import { GeoSource } from "@/lib/geolocation/types";

/**
 * Configuración de tiles para Leaflet
 * Soporta múltiples providers: OpenStreetMap (default)
 */

// OpenStreetMap - Proveedor gratuito por defecto
export const OPENSTREETMAP_TILES = {
  url: "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
  attribution:
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  maxZoom: 19,
  minZoom: 1,
};

// Alternativa: Maptiler (mejor estética, plan gratuito)
export const MAPTILER_TILES = {
  url: "https://api.maptiler.com/maps/openstreetmap/{z}/{x}/{y}.png?key=YOUR_API_KEY",
  attribution:
    '&copy; <a href="https://www.maptiler.com/">MapTiler</a> &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
  maxZoom: 19,
  minZoom: 1,
};

/**
 * Proveedor de tiles por defecto para MVP (OpenStreetMap)
 * Cambiar a MAPTILER_TILES si OpenStreetMap tiene issues
 */
export const DEFAULT_TILE_PROVIDER = OPENSTREETMAP_TILES;

/**
 * Zoom levels por fuente de geolocalización
 * GPS: más cercano (ciudad)
 * IP: más lejano (región)
 */
export const ZOOM_LEVELS = {
  gps: 15, // Precisión en metros, zoom a nivel de calle
  ip: 12, // Precisión en km, zoom a nivel de ciudad/región
};

/**
 * Calcula el zoom level apropiado según la fuente de ubicación
 */
export function calculateZoomLevel(source: GeoSource): number {
  return ZOOM_LEVELS[source] || ZOOM_LEVELS.ip;
}

/**
 * Configuración del mapa por defecto
 */
export const MAP_CONFIG = {
  zoom: ZOOM_LEVELS.gps,
  minZoom: 3,
  maxZoom: 19,
  zoomControl: true,
  scrollWheelZoom: true,
  dragging: true,
  touchZoom: true,
  doubleClickZoom: true,
  boxZoom: true,
  keyboard: true,
  tap: true,
  tapTolerance: 15,
  // Bounds: opcional - si quieres restringir a cierta región
  // bounds: [[min_lat, min_lng], [max_lat, max_lng]],
};

/**
 * Limites de validación de coordenadas (WGS84)
 */
export const COORDINATE_BOUNDS = {
  minLat: -90,
  maxLat: 90,
  minLng: -180,
  maxLng: 180,
};

/**
 * Validar que las coordenadas estén dentro de rangos válidos
 */
export function isValidCoordinates(lat: number, lng: number): boolean {
  return (
    lat >= COORDINATE_BOUNDS.minLat &&
    lat <= COORDINATE_BOUNDS.maxLat &&
    lng >= COORDINATE_BOUNDS.minLng &&
    lng <= COORDINATE_BOUNDS.maxLng
  );
}

/**
 * Mensaje de atribución completo
 */
export const ATTRIBUTION_TEXT =
  'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, powered by <a href="https://leafletjs.com/">Leaflet</a>';
