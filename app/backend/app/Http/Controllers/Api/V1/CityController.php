<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\CityService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CityRequest;
use App\Http\Resources\Api\V1\CityResource;
use App\Http\Requests\Api\V1\DeleteCityRequest;
use App\Http\Requests\Api\V1\IndexCityRequest;
use App\Http\Requests\Api\V1\ShowCityRequest;
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
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *       ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
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
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/cities",
     *      operationId="storeCity",
     *      tags={"Cities"},
     *      summary="Store new city",
     *      description="Returns city data",
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
     *       ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     *
     * @return CityResource
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
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/cities/{id}",
     *      operationId="getCityById",
     *      tags={"Cities"},
     *      summary="Get city information",
     *      description="Returns city data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="City id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *       ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
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
     * Update the specified resource in storage.
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
     *          description="City id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
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
     *       ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
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
     * Remove the specified resource from storage.
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
     *          description="City id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="City deleted successfully")
     *          )
     *       ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
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
