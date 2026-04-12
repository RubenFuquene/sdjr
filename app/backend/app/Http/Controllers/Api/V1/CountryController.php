<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CountryRequest;
use App\Http\Requests\Api\V1\IndexCountryRequest;
use App\Http\Requests\Api\V1\ShowCountryRequest;
use App\Http\Resources\Api\V1\CountryResource;
use App\Services\CountryService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Countries",
 *     description="API Endpoints of Countries"
 * )
 */
class CountryController extends Controller
{
    use ApiResponseTrait;

    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/countries",
     *     operationId="getCountriesList",
     *     tags={"Countries"},
     *     summary="Get list of countries",
     *     description="Returns list of countries. Permite filtrar por nombre (name), código (code), estado (status: 1=activos, 0=inactivos, all=todos) y número de registros por página (per_page).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false, description="Filtrar por nombre del país (texto parcial)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="code", in="query", required=false, description="Filtrar por código del país (ISO)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filtrar por estado: 1=activos, 0=inactivos, all=todos", @OA\Schema(type="string", enum={"1","0","all"}, default="all")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Cantidad de registros por página (1-100)", @OA\Schema(type="integer", default=15)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CountryResource")))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(
     *         response=429,
     *         description="Too Many Requests",
     *
     *         @OA\JsonContent(example={"status":false,"message":"Too many requests. Please try again later.","code":429}),
     *
     *         @OA\Header(header="Retry-After", description="Segundos hasta que se puede volver a intentar", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-RateLimit-Limit", description="Límite de peticiones por ventana", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-RateLimit-Remaining", description="Peticiones restantes en la ventana actual", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-RateLimit-Reset", description="Timestamp de reseteo de ventana", @OA\Schema(type="integer"))
     *     )
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function index(IndexCountryRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $countries = $this->countryService->getPaginated($filters, $perPage);
            $resource = CountryResource::collection($countries);

            return $this->paginatedResponse($countries, $resource, 'Countries retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving countries', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created country.
     *
     * @OA\Post(
     *     path="/api/v1/countries",
     *     operationId="storeCountry",
     *     tags={"Countries"},
     *     summary="Store new country",
     *     description="Returns created country data",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *
     *     @OA\Response(response=201, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/CountryResource")),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(CountryRequest $request): CountryResource|JsonResponse
    {
        try {
            $country = $this->countryService->create($request->validated());

            return $this->successResponse(new CountryResource($country), 'Country created successfully', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error creating country', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified country.
     *
     * @OA\Get(
     *     path="/api/v1/countries/{id}",
     *     operationId="showCountry",
     *     tags={"Countries"},
     *     summary="Get country information",
     *     description="Returns country data",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Country ID", @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/CountryResource")),
     *     @OA\Response(response=404, description="Resource Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(ShowCountryRequest $request, string $id): CountryResource|JsonResponse
    {
        try {
            $country = $this->countryService->find($id);
            if (! $country) {
                return $this->errorResponse('Country not found', 404);
            }

            return $this->successResponse(new CountryResource($country), 'Country retrieved successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving country', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified country.
     *
     * @OA\Put(
     *     path="/api/v1/countries/{id}",
     *     operationId="updateCountry",
     *     tags={"Countries"},
     *     summary="Update existing country",
     *     description="Returns updated country data",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Country ID", @OA\Schema(type="string")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(type="object")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/CountryResource")),
     *     @OA\Response(response=404, description="Resource Not Found"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(CountryRequest $request, string $id): CountryResource|JsonResponse
    {
        try {
            $country = $this->countryService->find($id);
            if (! $country) {
                return $this->errorResponse('Country not found', 404);
            }
            $updatedCountry = $this->countryService->update($country, $request->validated());

            return $this->successResponse(new CountryResource($updatedCountry), 'Country updated successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error updating country', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified country.
     *
     * @OA\Delete(
     *     path="/api/v1/countries/{id}",
     *     operationId="deleteCountry",
     *     tags={"Countries"},
     *     summary="Delete existing country",
     *     description="Deletes a record and returns no content",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Country ID", @OA\Schema(type="string")),
     *
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $country = $this->countryService->find($id);
            if (! $country) {
                return $this->errorResponse('Country not found', 404);
            }
            $this->countryService->delete($country);

            return $this->noContentResponse();
        } catch (\Throwable $e) {
            return $this->errorResponse('Error deleting country', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}
