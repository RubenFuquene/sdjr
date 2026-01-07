<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CityRequest;
use App\Http\Requests\Api\V1\DeleteCityRequest;
use App\Http\Requests\Api\V1\IndexCityRequest;
use App\Http\Requests\Api\V1\ShowCityRequest;
use App\Http\Resources\Api\V1\CityResource;
use App\Services\CityService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Cities",
 *     description="API Endpoints of Cities"
 * )
 */
class CityController extends Controller
{
    use ApiResponseTrait;

    protected CityService $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/cities",
     *      operationId="getCitiesList",
     *      tags={"Cities"},
     *      summary="Get list of cities",
     *      description="Returns list of cities",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          required=false,
     *          description="Items per page",
     *
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          required=false,
     *          description="Filter by status (1=active, 0=inactive)",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CityResource")),
     *              @OA\Property(property="meta", type="object"),
     *              @OA\Property(property="links", type="object")
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function index(IndexCityRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = $request->validatedPerPage();
            $status = $request->validatedStatus();
            $cities = $this->cityService->getPaginated($perPage, $status);
            $resource = CityResource::collection($cities);

            return $this->paginatedResponse($cities, $resource, 'Cities retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving cities', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created city.
     *
     * @OA\Post(
     *      path="/api/v1/cities",
     *      operationId="storeCity",
     *      tags={"Cities"},
     *      summary="Store new city",
     *      description="Returns created city data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CityRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *      ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(CityRequest $request): CityResource|JsonResponse
    {
        try {
            $city = $this->cityService->create($request->validated());

            return $this->successResponse(new CityResource($city), 'City created successfully', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error creating city', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified city.
     *
     * @OA\Get(
     *      path="/api/v1/cities/{id}",
     *      operationId="showCity",
     *      tags={"Cities"},
     *      summary="Get city information",
     *      description="Returns city data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="City ID",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *      ),
     *
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(ShowCityRequest $request, string $id): CityResource|JsonResponse
    {
        try {
            $city = $this->cityService->find($id);

            return $this->successResponse(new CityResource($city), 'City retrieved successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving city', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified city.
     *
     * @OA\Put(
     *      path="/api/v1/cities/{id}",
     *      operationId="updateCity",
     *      tags={"Cities"},
     *      summary="Update existing city",
     *      description="Returns updated city data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="City ID",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CityRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *      ),
     *
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(CityRequest $request, string $id): CityResource|JsonResponse
    {
        try {
            $city = $this->cityService->find($id);
            if (! $city) {
                return $this->errorResponse('City not found', 404);
            }
            $updatedCity = $this->cityService->update($city, $request->validated());

            return $this->successResponse(new CityResource($updatedCity), 'City updated successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error updating city', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified city.
     *
     * @OA\Delete(
     *      path="/api/v1/cities/{id}",
     *      operationId="deleteCity",
     *      tags={"Cities"},
     *      summary="Delete existing city",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="City ID",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(response=204, description="No Content"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(DeleteCityRequest $request, string $id): JsonResponse
    {
        try {
            $city = $this->cityService->find($id);
            if (! $city) {
                return $this->errorResponse('City not found', 404);
            }
            $this->cityService->delete($city);

            return $this->noContentResponse();
        } catch (\Throwable $e) {
            return $this->errorResponse('Error deleting city', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}
