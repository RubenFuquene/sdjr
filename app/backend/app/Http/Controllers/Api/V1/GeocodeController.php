<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GeocodeReverseRequest;
use App\Http\Requests\Api\V1\GeocodeSearchRequest;
use App\Services\GeocodingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * @OA\Tag(
 *     name="Geocode",
 *     description="Proxy de geocoding/reverse geocoding (Nominatim/OpenStreetMap)"
 * )
 */
class GeocodeController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly GeocodingService $geocodingService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/geocode",
     *     operationId="geocodeSearch",
     *     tags={"Geocode"},
     *     summary="Geocoding directo: dirección de texto → punto geográfico",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="q", in="query", required=true, description="Dirección de texto a geocodificar", @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity")
     * )
     */
    public function search(GeocodeSearchRequest $request): JsonResponse
    {
        try {
            $result = $this->geocodingService->geocode($request->validatedQuery());

            if ($result === null) {
                return $this->errorResponse('No se encontraron resultados para la dirección indicada', 404);
            }

            return $this->successResponse($result, 'Geocoding realizado correctamente');
        } catch (Throwable $e) {
            return $this->errorResponse('Error al geocodificar la dirección', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/geocode/reverse",
     *     operationId="geocodeReverse",
     *     tags={"Geocode"},
     *     summary="Geocoding inverso: punto geográfico → dirección aproximada",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="lat", in="query", required=true, description="Latitud", @OA\Schema(type="number", format="float")),
     *     @OA\Parameter(name="lng", in="query", required=true, description="Longitud", @OA\Schema(type="number", format="float")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity")
     * )
     */
    public function reverse(GeocodeReverseRequest $request): JsonResponse
    {
        try {
            $result = $this->geocodingService->reverseGeocode($request->validatedLat(), $request->validatedLng());

            if ($result === null) {
                return $this->errorResponse('No se encontró información para las coordenadas indicadas', 404);
            }

            return $this->successResponse($result, 'Geocoding inverso realizado correctamente');
        } catch (Throwable $e) {
            return $this->errorResponse('Error al geocodificar las coordenadas', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}
