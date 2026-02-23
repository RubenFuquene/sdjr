/**
 * Tipos base para la capa de geolocalización
 * MVP: Browser Geolocation + Fallback por IP (ipwho.is)
 * Escalable: Permite cambiar providers sin tocar UI/hooks
 */

/**
 * Fuente de ubicación del usuario
 */
export type GeoSource = "gps" | "ip";

/**
 * Códigos de error de geolocalización normalizados
 */
export enum LocationErrorCode {
  PERMISSION_DENIED = "PERMISSION_DENIED",
  POSITION_UNAVAILABLE = "POSITION_UNAVAILABLE",
  TIMEOUT = "TIMEOUT",
  UNKNOWN = "UNKNOWN",
  NETWORK_ERROR = "NETWORK_ERROR",
  INVALID_RESPONSE = "INVALID_RESPONSE",
}

/**
 * Ubicación del usuario con metadatos
 */
export interface UserLocation {
  // Coordenadas (grados decimales)
  lat: number;
  lng: number;

  // Precisión en metros (solo presente si source="gps")
  accuracy?: number;

  // Fuente de datos
  source: GeoSource;

  // Información geográfica (disponible según proveedor)
  city?: string;
  region?: string;
  country?: string;
  countryCode?: string;

  // Timestamp ISO cuando se obtuvo la ubicación
  obtainedAt: string;

  // ID único para seguimiento (opcional, útil para telemetría)
  requestId?: string;
}

/**
 * Error de geolocalización con contexto
 */
export interface GeoLocationError {
  code: LocationErrorCode;
  message: string;
  details?: Record<string, unknown>;
  source?: GeoSource;
}

/**
 * Interfaz de proveedor de geolocalización
 * Permite cambiar entre Web API, Capacitor, o cualquier otra fuente
 */
export interface GeoProvider {
  /**
   * Obtiene la ubicación desde este proveedor
   * @param options Opciones específicas del proveedor
   * @returns Promise con ubicación o error
   */
  getLocation(options?: GeoProviderOptions): Promise<UserLocation>;

  /**
   * Verifica si este proveedor está disponible en el entorno actual
   */
  isAvailable(): boolean;

  /**
   * Nombre del proveedor para logging/telemetría
   */
  name: string;
}

/**
 * Opciones comunes para proveedores de geolocalización
 */
export interface GeoProviderOptions {
  /** Timeout máximo en milisegundos */
  timeoutMs?: number;

  /** Si es posible, usar alta precisión (GPS) */
  enableHighAccuracy?: boolean;

  /** ID único para seguimiento de esta solicitud */
  requestId?: string;
}

/**
 * Respuesta de proveedor IP (ipwho.is)
 * Se mapea a UserLocation en el provider
 */
export interface IpGeoResponse {
  ip: string;
  success: boolean;
  type: string;
  continent: string;
  continent_code: string;
  country: string;
  country_code: string;
  region: string;
  region_code: string;
  city: string;
  latitude: number;
  longitude: number;
  is_eu: boolean;
  postal: string;
  calling_code: string;
  capital: string;
  borders: string;
  geonameid: string;
  timezones: string;
  languages: string;
  currency_code: string;
  currency_name: string;
  currency_symbol: string;
  connection_type: string;
  isp: string;
  organization: string;
  asn: string;
  message?: string;
}

/**
 * Estados posibles del hook useUserLocation
 */
export type LocationState = "idle" | "loading" | "ready" | "denied" | "error";

/**
 * Estado completo del hook de ubicación para UI
 */
export interface UseLocationResult {
  // Ubicación actual (null si no está disponible)
  location: UserLocation | null;

  // Estado actual del proceso
  state: LocationState;

  // Error actual (si state === "error")
  error: GeoLocationError | null;

  // Función para reintentar obtener ubicación
  refresh: () => Promise<void>;

  // Función para limpiar la ubicación en caché
  clear: () => void;

  // Indica si se puede reintentar (no es "idle" o "loading")
  canRetry: boolean;
}
