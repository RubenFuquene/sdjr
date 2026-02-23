import {
  GeoProvider,
  GeoProviderOptions,
  UserLocation,
  LocationErrorCode,
  GeoLocationError,
  IpGeoResponse,
} from "../types";

/**
 * Proveedor de geolocalización usando ipwho.is
 * Fallback que funciona sin permiso del usuario, solo basado en IP.
 * No requiere API key en el plan gratuito para MVP.
 */
export class IpGeoProvider implements GeoProvider {
  name = "IpGeo (ipwho.is)";

  private static readonly API_ENDPOINT = "https://ipwho.is/";
  private static readonly DEFAULT_TIMEOUT = 5000; // 5 segundos para IP lookup

  /**
   * Siempre disponible si hay conexión a internet
   * Se valida en tiempo de ejecución
   */
  isAvailable(): boolean {
    return typeof window !== "undefined" && !!fetch;
  }

  /**
   * Obtiene ubicación del usuario por su IP
   * No requiere permiso y es más rápido que GPS en casos de rechazo
   */
  async getLocation(options?: GeoProviderOptions): Promise<UserLocation> {
    if (!this.isAvailable()) {
      throw {
        code: LocationErrorCode.NETWORK_ERROR,
        message: "No hay conexión a internet o fetch no disponible",
        source: "ip",
      } as GeoLocationError;
    }

    try {
      const timeoutMs = options?.timeoutMs ?? IpGeoProvider.DEFAULT_TIMEOUT;
      const controller = new AbortController();
      const timeoutHandle = setTimeout(
        () => controller.abort(),
        timeoutMs
      );

      const response = await fetch(IpGeoProvider.API_ENDPOINT, {
        signal: controller.signal,
      });

      clearTimeout(timeoutHandle);

      if (!response.ok) {
        throw {
          code: LocationErrorCode.NETWORK_ERROR,
          message: `Error llamando a ipwho.is: ${response.statusText}`,
          details: { status: response.status },
          source: "ip",
        } as GeoLocationError;
      }

      const data = (await response.json()) as IpGeoResponse;

      // Validar que la respuesta tenga los campos necesarios
      if (!data.success || data.latitude === undefined || data.longitude === undefined) {
        throw {
          code: LocationErrorCode.INVALID_RESPONSE,
          message: "Respuesta de ipwho.is inválida o incompleta",
          details: { response: data },
          source: "ip",
        } as GeoLocationError;
      }

      // Mapear respuesta de ipwho.is a nuestro contrato UserLocation
      return this.mapResponseToUserLocation(data, options?.requestId);
    } catch (error) {
      // Si es nuestro error tipado, re-lanzar como está
      if (this.isGeoLocationError(error)) {
        throw error;
      }

      // Si es AbortError, fue timeout
      if (error instanceof Error && error.name === "AbortError") {
        throw {
          code: LocationErrorCode.TIMEOUT,
          message: `Timeout obteniendo ubicación por IP (${options?.timeoutMs ?? IpGeoProvider.DEFAULT_TIMEOUT}ms)`,
          source: "ip",
        } as GeoLocationError;
      }

      // Cualquier otro error (JSON parse, fetch error, etc)
      throw {
        code: LocationErrorCode.NETWORK_ERROR,
        message: `Error obteniendo ubicación por IP: ${error instanceof Error ? error.message : String(error)}`,
        details: { originalError: error },
        source: "ip",
      } as GeoLocationError;
    }
  }

  /**
   * Mapea la respuesta de ipwho.is al contrato UserLocation
   */
  private mapResponseToUserLocation(
    response: IpGeoResponse,
    requestId?: string
  ): UserLocation {
    return {
      lat: response.latitude,
      lng: response.longitude,
      source: "ip",
      city: response.city || undefined,
      region: response.region || undefined,
      country: response.country || undefined,
      countryCode: response.country_code || undefined,
      obtainedAt: new Date().toISOString(),
      requestId,
    };
  }

  /**
   * Type guard para verificar si un error es GeoLocationError
   */
  private isGeoLocationError(error: unknown): error is GeoLocationError {
    return (
      error !== null &&
      typeof error === "object" &&
      "code" in error &&
      "message" in error
    );
  }
}

/**
 * Instancia singleton del proveedor IP
 * Listo para ser inyectado o usado directamente como fallback
 */
export const ipGeoProvider = new IpGeoProvider();
