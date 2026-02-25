import {
  GeoProvider,
  GeoProviderOptions,
  UserLocation,
  LocationErrorCode,
  GeoLocationError,
} from "../types";

/**
 * Proveedor de geolocalización usando Web Geolocation API
 * Requiere permiso del usuario y HTTPS (o localhost en dev)
 * Funciona en navegadores modernos y PWA
 */
export class WebGeoProvider implements GeoProvider {
  name = "WebGeolocation";

  /**
   * Verifica si el API está disponible en el navegador actual
   */
  isAvailable(): boolean {
    return typeof navigator !== "undefined" && !!navigator.geolocation;
  }

  /**
   * Obtiene la ubicación del usuario usando el navegador
   * Requiere permiso y puede fallar por timeout, permiso denegado, o no disponibilidad
   */
  async getLocation(options?: GeoProviderOptions): Promise<UserLocation> {
    if (!this.isAvailable()) {
      throw {
        code: LocationErrorCode.POSITION_UNAVAILABLE,
        message:
          "Web Geolocation API no disponible en este navegador o entorno",
        source: "gps",
      } as GeoLocationError;
    }

    return new Promise((resolve, reject) => {
      const timeoutMs = options?.timeoutMs ?? 6000;
      const enableHighAccuracy = options?.enableHighAccuracy ?? true;

      // Timeout manual para asegurar que se cancela incluso con timeout del browser
      const timeoutHandle = setTimeout(() => {
        reject({
          code: LocationErrorCode.TIMEOUT,
          message: `Timeout obteniendo ubicación (${timeoutMs}ms)`,
          source: "gps",
        } as GeoLocationError);
      }, timeoutMs);

      navigator.geolocation.getCurrentPosition(
        (position) => {
          clearTimeout(timeoutHandle);

          const { latitude, longitude, accuracy } = position.coords;

          resolve({
            lat: latitude,
            lng: longitude,
            accuracy,
            source: "gps",
            obtainedAt: new Date().toISOString(),
            requestId: options?.requestId,
          });
        },
        (error) => {
          clearTimeout(timeoutHandle);
          reject(this.normalizeError(error));
        },
        {
          enableHighAccuracy,
          timeout: timeoutMs,
          maximumAge: 0, // No usar caché de browser, obtener posición fresca
        }
      );
    });
  }

  /**
   * Normaliza errores de GeolocationPositionError a nuestro enum
   * Mapea los códigos genéricos del browser a nuestro contrato de errores
   */
  private normalizeError(error: GeolocationPositionError): GeoLocationError {
    let code: LocationErrorCode;
    let message: string;

    switch (error.code) {
      case error.PERMISSION_DENIED:
        code = LocationErrorCode.PERMISSION_DENIED;
        message = "Permiso de ubicación denegado por el usuario";
        break;

      case error.POSITION_UNAVAILABLE:
        code = LocationErrorCode.POSITION_UNAVAILABLE;
        message =
          "La posición no está disponible (WiFi/GPS/celda no disponibles)";
        break;

      case error.TIMEOUT:
        code = LocationErrorCode.TIMEOUT;
        message = "Timeout obteniendo la posición del navegador";
        break;

      default:
        code = LocationErrorCode.UNKNOWN;
        message = "Error desconocido obteniendo la posición";
    }

    return {
      code,
      message,
      details: {
        originalCode: error.code,
        originalMessage: error.message,
      },
      source: "gps",
    };
  }
}

/**
 * Instancia singleton del proveedor Web
 * Listo para ser inyectado o usado directamente
 */
export const webGeoProvider = new WebGeoProvider();
