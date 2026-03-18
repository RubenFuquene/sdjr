<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NearbyBranchesRequest;
use App\Http\Requests\Api\V1\NearbyProductsRequest;
use App\Http\Resources\Api\V1\NearbyBranchResource;
use App\Http\Resources\Api\V1\NearbyProductResource;
use App\Services\NearbySearchService;
use App\Traits\ApiResponseTrait;

class NearbyController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly NearbySearchService $nearbySearchService) {}

    /** @OA\Get(
     * path="/api/v1/nearby/branches",
     * summary="Sucursales cercanas a una ubicación",
     * tags={"Nearby"},
     *
     * @OA\Parameter(name="latitude", in="query", required=true, description="Latitud de la ubicación actual", @OA\Schema(type="number", format="float", example=19.4326)),
     * @OA\Parameter(name="longitude", in="query", required=true, description="Longitud de la ubicación actual", @OA\Schema(type="number", format="float", example=-99.1332)),
     * @OA\Parameter(name="radius", in="query", required=false, description="Radio de búsqueda en kilómetros", @OA\Schema(type="integer", example=10)),
     * @OA\Parameter(name="per_page", in="query", required=false, description="Cantidad de resultados por página", @OA\Schema(type="integer", example=15)),
     *
     * @OA\Response(response=200, description="Lista paginada de sucursales ordenadas por distancia", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/NearbyBranchResource")), @OA\Property(property="links", type="object"), @OA\Property(property="meta", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="total", type="integer")))),
     * @OA\Response(response=422, description="Error de validación"),
     * @OA\Response(response=500, description="Error interno")
     * ) */
    public function branches(NearbyBranchesRequest $request)
    {
        try {
            $branches = $this->nearbySearchService->nearbyBranches(
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                $request->validatedRadius(),
                $request->validatedPerPage()
            );
            $resources = NearbyBranchResource::collection($branches);

            return $this->paginatedResponse($branches, $resources, 'Nearby branches retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error al obtener sucursales cercanas', 500, [$e->getMessage()]);
        }
    }

    /** @OA\Get(
     * path="/api/v1/nearby/products",
     * summary="Productos disponibles en sucursales cercanas",
     * tags={"Nearby"},
     *
     * @OA\Parameter(name="latitude", in="query", required=true, description="Latitud de la ubicación actual", @OA\Schema(type="number", format="float", example=19.4326)),
     * @OA\Parameter(name="longitude", in="query", required=true, description="Longitud de la ubicación actual", @OA\Schema(type="number", format="float", example=-99.1332)),
     * @OA\Parameter(name="radius", in="query", required=false, description="Radio de búsqueda en kilómetros", @OA\Schema(type="integer", example=10)),
     * @OA\Parameter(name="category_id", in="query", required=false, description="ID de la categoría del producto", @OA\Schema(type="integer", example=5)),
     * @OA\Parameter(name="max_price", in="query", required=false, description="Precio máximo del producto", @OA\Schema(type="number", format="float", example=100.00)),
     * @OA\Parameter(name="per_page", in="query", required=false, description="Cantidad de resultados por página", @OA\Schema(type="integer", example=15)),
     *
     * @OA\Response(response=200, description="Lista paginada de productos con distancia a la sucursal más cercana", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/NearbyProductResource")), @OA\Property(property="links", type="object"), @OA\Property(property="meta", type="object", @OA\Property(property="current_page", type="integer"), @OA\Property(property="last_page", type="integer"), @OA\Property(property="per_page", type="integer"), @OA\Property(property="total", type="integer")))),
     * @OA\Response(response=422, description="Error de validación"),
     * @OA\Response(response=500, description="Error interno")
     * ) */
    public function products(NearbyProductsRequest $request)
    {
        try {
            $products = $this->nearbySearchService->nearbyProducts(
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                $request->validatedRadius(),
                $request->filters(),
                $request->validatedPerPage()
            );
            $resources = NearbyProductResource::collection($products);

            return $this->paginatedResponse($products, $resources, 'Nearby products retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error al obtener productos cercanos', 500, [$e->getMessage()]);
        }
    }
}
