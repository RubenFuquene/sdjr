import L from "leaflet";
import { GeoSource, UserLocation } from "@/lib/geolocation/types";
import {
  calculateZoomLevel as getZoomLevel,
  isValidCoordinates,
} from "@/lib/maps/tile-configs";
import {
  createGpsMarkerIcon,
  createIpMarkerIcon,
  createCommerceMarkerIcon,
  COLORS,
} from "@/lib/maps/constants";

/**
 * Utilidades puras para lógica de mapas.
 *
 * Guía de mantenimiento:
 * - Mantener estas funciones side-effect free para facilitar testeo y reuso.
 * - Evitar mover lógica de UI/JSX aquí; este módulo solo calcula/transforma datos.
 * - Nuevas utilidades para comercios (clustering, filtros por radio) deben vivir aquí.
 *
 * Referencias:
 * - Leaflet LatLng/Bounds: https://leafletjs.com/reference.html
 * - React-Leaflet docs: https://react-leaflet.js.org/
 */

/**
 * Calcula el zoom level apropiado según la fuente de geolocalización
 */
export function calculateZoomLevel(source: GeoSource): number {
  return getZoomLevel(source);
}

/**
 * Obtiene el icono de marcador correcto según la fuente de ubicación
 */
export function getUserMarkerIcon(source: GeoSource): L.Icon {
  switch (source) {
    case "gps":
      return createGpsMarkerIcon();
    case "ip":
      return createIpMarkerIcon();
    default:
      return createGpsMarkerIcon();
  }
}

/**
 * Obtiene el icono de comercio
 * @param isSelected - Si el comercio está seleccionado
 */
export function getCommerceMarkerIcon(isSelected: boolean = false): L.Icon {
  return createCommerceMarkerIcon(isSelected);
}

/**
 * Valida que las coordenadas sean válidas (WGS84)
 * @returns true si las coordenadas están dentro de rangos válidos
 */
export function isValidLocation(lat: number, lng: number): boolean {
  return isValidCoordinates(lat, lng);
}

/**
 * Valida que una ubicación completa sea válida
 */
export function isValidUserLocation(location: UserLocation | null): boolean {
  if (!location) return false;
  return isValidLocation(location.lat, location.lng);
}

/**
 * Calcula la distancia entre dos puntos en metros usando fórmula de Haversine
 * @param lat1 - Latitud del primer punto
 * @param lng1 - Longitud del primer punto
 * @param lat2 - Latitud del segundo punto
 * @param lng2 - Longitud del segundo punto
 * @returns Distancia en metros
 */
export function calculateDistance(
  lat1: number,
  lng1: number,
  lat2: number,
  lng2: number
): number {
  const R = 6371000; // Radio de la Tierra en metros
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLng = ((lng2 - lng1) * Math.PI) / 180;

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLng / 2) *
      Math.sin(dLng / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

/**
 * Formatea un número de distancia de forma legible
 * @param distanceMeters - Distancia en metros
 * @returns String formateado (ej: "1.5 km" o "250 m")
 */
export function formatDistance(distanceMeters: number): string {
  if (distanceMeters < 1000) {
    return `${Math.round(distanceMeters)} m`;
  }
  return `${(distanceMeters / 1000).toFixed(1)} km`;
}

/**
 * Formatea coordenadas para mostrar en UI
 * @param lat - Latitud
 * @param lng - Longitud
 * @param decimals - Número de decimales (default: 4)
 * @returns String formateado (ej: "4.7110, -74.0721")
 */
export function formatCoordinates(
  lat: number,
  lng: number,
  decimals: number = 4
): string {
  return `${lat.toFixed(decimals)}, ${lng.toFixed(decimals)}`;
}

/**
 * Obtiene una descripción legible de la ubicación
 * @param location - UserLocation
 * @returns Descripción formateada (ej: "Bogotá, Colombia")
 */
export function getLocationDescription(location: UserLocation): string {
  const parts = [];

  if (location.city) parts.push(location.city);
  if (location.region && location.region !== location.city) parts.push(location.region);
  if (location.country) parts.push(location.country);

  if (parts.length === 0) {
    return formatCoordinates(location.lat, location.lng);
  }

  return parts.join(", ");
}

/**
 * Calcula los bounds (limites) de un array de coordenadas
 * Útil para mostrar múltiples marcadores en el mapa
 * @param locations - Array de ubicaciones [lat, lng]
 * @returns LatLngBounds de Leaflet
 */
export function calculateBounds(
  locations: Array<[number, number]>
): L.LatLngBounds {
  if (locations.length === 0) {
    // Centro por defecto (mundo)
    return L.latLngBounds([0, 0], [0, 0]);
  }

  if (locations.length === 1) {
    const [lat, lng] = locations[0];
    return L.latLngBounds([lat, lng], [lat, lng]);
  }

  return L.latLngBounds(locations);
}

/**
 * Obtiene el centro de un array de coordenadas
 * @param locations - Array de ubicaciones [lat, lng]
 * @returns [lat, lng] del centro
 */
export function getCenter(locations: Array<[number, number]>): [number, number] {
  if (locations.length === 0) {
    return [0, 0];
  }

  const sum = locations.reduce(
    (acc, [lat, lng]) => [acc[0] + lat, acc[1] + lng],
    [0, 0]
  );

  return [sum[0] / locations.length, sum[1] / locations.length];
}

/**
 * Determina si un punto está dentro de un radio de otro
 * @param centerLat - Latitud del centro
 * @param centerLng - Longitud del centro
 * @param pointLat - Latitud del punto
 * @param pointLng - Longitud del punto
 * @param radiusMeters - Radio en metros
 * @returns true si el punto está dentro del radio
 */
export function isWithinRadius(
  centerLat: number,
  centerLng: number,
  pointLat: number,
  pointLng: number,
  radiusMeters: number
): boolean {
  const distance = calculateDistance(centerLat, centerLng, pointLat, pointLng);
  return distance <= radiusMeters;
}

/**
 * Convierte un bearing (ángulo) a dirección cardinal
 * @param bearing - Ángulo en grados (0-360)
 * @returns Dirección cardinal (ej: "N", "NE", "E", etc)
 */
export function bearingToCardinal(bearing: number): string {
  const directions = ["N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW"];
  const index = Math.round(bearing / 22.5) % 16;
  return directions[index];
}

/**
 * Calcula el bearing (ángulo) entre dos puntos
 * @param lat1 - Latitud del primer punto
 * @param lng1 - Longitud del primer punto
 * @param lat2 - Latitud del segundo punto
 * @param lng2 - Longitud del segundo punto
 * @returns Bearing en grados (0-360)
 */
export function calculateBearing(
  lat1: number,
  lng1: number,
  lat2: number,
  lng2: number
): number {
  const dLng = ((lng2 - lng1) * Math.PI) / 180;
  const y = Math.sin(dLng) * Math.cos((lat2 * Math.PI) / 180);
  const x =
    Math.cos((lat1 * Math.PI) / 180) * Math.sin((lat2 * Math.PI) / 180) -
    Math.sin((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.cos(dLng);

  let bearing = Math.atan2(y, x) * (180 / Math.PI);
  bearing = (bearing + 360) % 360;
  return bearing;
}

/**
 * Obtiene un color apropiado según el tipo de marcador
 */
export function getMarkerColor(type: "user" | "user-ip" | "commerce" = "user"): string {
  switch (type) {
    case "user":
      return COLORS.userGps;
    case "user-ip":
      return COLORS.userIp;
    case "commerce":
      return COLORS.commerce;
    default:
      return COLORS.userGps;
  }
}

/**
 * Crea un L.LatLng a partir de una ubicación
 */
export function locationToLatLng(location: UserLocation): L.LatLng {
  return L.latLng(location.lat, location.lng);
}

/**
 * Crea coordenadas [lat, lng] a partir de una ubicación
 */
export function locationToArray(location: UserLocation): [number, number] {
  return [location.lat, location.lng];
}
