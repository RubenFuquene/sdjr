<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Proxy de geocoding hacia Nominatim (OpenStreetMap).
 *
 * Nunca se expone la llamada directa al navegador: este servicio identifica
 * la app con un User-Agent válido, cachea resultados y serializa las
 * solicitudes salientes para respetar el límite de ~1 req/s de la política
 * de uso de Nominatim (https://operations.osmfoundation.org/policies/nominatim/).
 */
class GeocodingService
{
    private const CACHE_TTL_SECONDS = 60 * 60 * 24 * 7; // 7 días

    private const THROTTLE_LOCK_KEY = 'nominatim:throttle:lock';

    private const LAST_REQUEST_CACHE_KEY = 'nominatim:throttle:last_request_at';

    private const MIN_INTERVAL_MS = 1100;

    // Bounding box aproximado de Bogotá D.C. (west,north,east,south) — MVP.
    private const BOGOTA_VIEWBOX = '-74.223,4.837,-73.988,4.471';

    /**
     * Geocoding directo: dirección de texto → punto geográfico.
     *
     * @return array<string, mixed>|null
     */
    public function geocode(string $query): ?array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return null;
        }

        $cacheKey = 'geocode:search:'.md5(mb_strtolower($normalizedQuery));

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($normalizedQuery) {
            $response = $this->request('search', [
                'q' => $normalizedQuery,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => 1,
                'countrycodes' => 'co',
                'accept-language' => 'es',
                'viewbox' => self::BOGOTA_VIEWBOX,
                'bounded' => 1,
            ]);

            if (! is_array($response) || $response === [] || ! isset($response[0])) {
                return null;
            }

            return $this->normalizeResult($response[0]);
        });
    }

    /**
     * Geocoding inverso: punto geográfico → dirección/barrio/ciudad aproximados.
     *
     * @return array<string, mixed>|null
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $roundedLat = round($lat, 5);
        $roundedLng = round($lng, 5);
        $cacheKey = "geocode:reverse:{$roundedLat}:{$roundedLng}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($roundedLat, $roundedLng) {
            $response = $this->request('reverse', [
                'lat' => $roundedLat,
                'lon' => $roundedLng,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'accept-language' => 'es',
                'zoom' => 18,
            ]);

            if (! is_array($response) || isset($response['error'])) {
                return null;
            }

            return $this->normalizeResult($response);
        });
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    private function request(string $endpoint, array $params): ?array
    {
        $this->throttle();

        $contactEmail = config('services.nominatim.contact_email');
        if (! empty($contactEmail)) {
            $params['email'] = $contactEmail;
        }

        try {
            $baseUrl = rtrim((string) config('services.nominatim.base_url'), '/');

            $response = Http::withHeaders([
                'User-Agent' => config('services.nominatim.user_agent'),
            ])
                ->timeout(5)
                ->get("{$baseUrl}/{$endpoint}", $params);

            if ($response->failed()) {
                Log::warning('Nominatim request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (Throwable $e) {
            Log::warning('Nominatim request error', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Serializa las solicitudes salientes a Nominatim y respeta ~1 req/s,
     * incluso con múltiples solicitudes concurrentes al proxy.
     */
    private function throttle(): void
    {
        Cache::lock(self::THROTTLE_LOCK_KEY, 10)->block(10, function () {
            $lastRequestAtMs = Cache::get(self::LAST_REQUEST_CACHE_KEY);
            $nowMs = (int) (microtime(true) * 1000);

            if (is_int($lastRequestAtMs)) {
                $elapsedMs = $nowMs - $lastRequestAtMs;
                if ($elapsedMs < self::MIN_INTERVAL_MS) {
                    usleep((self::MIN_INTERVAL_MS - $elapsedMs) * 1000);
                }
            }

            Cache::put(self::LAST_REQUEST_CACHE_KEY, (int) (microtime(true) * 1000), 60);
        });
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeResult(array $item): array
    {
        $address = $item['address'] ?? [];

        return [
            'lat' => isset($item['lat']) ? (float) $item['lat'] : null,
            'lng' => isset($item['lon']) ? (float) $item['lon'] : null,
            'display_name' => $item['display_name'] ?? null,
            'address' => [
                'road' => $address['road'] ?? null,
                'house_number' => $address['house_number'] ?? null,
                'neighborhood' => $this->resolveNeighborhood($address),
                'city' => $address['city'] ?? $address['town'] ?? $address['municipality'] ?? null,
                'state' => $address['state'] ?? null,
            ],
        ];
    }

    /**
     * Devuelve el primer valor que sea un barrio real entre neighbourhood/
     * suburb/quarter, descartando artefactos administrativos de OSM que no son
     * barrios (UPZ = Unidad de Planeamiento Zonal, Localidad). Sin barrio real
     * disponible retorna null — no es un match que nuestro catálogo pueda usar.
     *
     * @param  array<string, mixed>  $address
     */
    private function resolveNeighborhood(array $address): ?string
    {
        foreach (['neighbourhood', 'suburb', 'quarter'] as $key) {
            $value = $address[$key] ?? null;
            if (is_string($value) && trim($value) !== '' && ! $this->isPlaceholderNeighborhood($value)) {
                return $value;
            }
        }

        return null;
    }

    private function isPlaceholderNeighborhood(string $value): bool
    {
        $normalized = mb_strtolower(trim($value));

        return str_starts_with($normalized, 'upz')
            || str_starts_with($normalized, 'localidad');
    }
}
