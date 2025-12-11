<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CityRequest;
use App\Http\Resources\Api\V1\CityResource;
use App\Services\CityService;
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
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *       ),
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
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $cities = $this->cityService->getPaginated();
            return CityResource::collection($cities);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error retrieving cities',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
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
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CityRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *       ),
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
     * @param CityRequest $request
     * @return CityResource
     */
    public function store(CityRequest $request): CityResource|JsonResponse
    {
        try {
            $city = $this->cityService->create($request->validated());
            return new CityResource($city);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error creating city',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
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
     *      @OA\Parameter(
     *          name="id",
     *          description="City id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *       ),
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
     *
     * @param string $id
     * @return CityResource|JsonResponse
     */
    public function show(string $id): CityResource|JsonResponse
    {
        try {
            $city = $this->cityService->find($id);
            if (!$city) {
                return response()->json(['message' => 'City not found'], 404);
            }
            return new CityResource($city);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error retrieving city',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
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
     *      @OA\Parameter(
     *          name="id",
     *          description="City id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CityRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/CityResource")
     *       ),
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
     *
     * @param CityRequest $request
     * @param string $id
     * @return CityResource|JsonResponse
     */
    public function update(CityRequest $request, string $id): CityResource|JsonResponse
    {
        try {
            $city = $this->cityService->find($id);
            if (!$city) {
                return response()->json(['message' => 'City not found'], 404);
            }
            $updatedCity = $this->cityService->update($city, $request->validated());
            return new CityResource($updatedCity);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error updating city',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
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
     *      @OA\Parameter(
     *          name="id",
     *          description="City id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="City deleted successfully")
     *          )
     *       ),
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
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $city = $this->cityService->find($id);
            if (!$city) {
                return response()->json(['message' => 'City not found'], 404);
            }
            $this->cityService->delete($city);
            return response()->json(['message' => 'City deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error deleting city',
                'error' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }
}
