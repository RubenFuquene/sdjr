<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NeighborhoodRequest;
use App\Http\Resources\Api\V1\NeighborhoodResource;
use App\Models\Neighborhood;
use App\Services\NeighborhoodService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Tag(
 *     name="Neighborhoods",
 *     description="API Endpoints of Neighborhoods"
 * )
 */
class NeighborhoodController extends Controller
{
    use ApiResponseTrait;

    private NeighborhoodService $neighborhoodService;

    public function __construct(NeighborhoodService $service)
    {
        $this->neighborhoodService = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/neighborhoods",
     *     operationId="indexNeighborhoods",
     *     tags={"Neighborhoods"},
     *     summary="List neighborhoods",
     *     description="Get paginated list of neighborhoods",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) ($request->get('per_page', 15));
            $neighborhoods = $this->neighborhoodService->getPaginated($perPage);
            $resource = NeighborhoodResource::collection($neighborhoods);

            return $this->paginatedResponse($neighborhoods, $resource, 'Neighborhoods retrieved successfully');
        } catch (Throwable $e) {
            return $this->errorResponse('Error fetching neighborhoods', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/neighborhoods",
     *     operationId="storeNeighborhood",
     *     tags={"Neighborhoods"},
     *     summary="Create neighborhood",
     *     description="Store a new neighborhood",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/NeighborhoodRequest")),
     *
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/NeighborhoodResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(NeighborhoodRequest $request): JsonResponse
    {
        try {
            $neighborhood = $this->neighborhoodService->store($request->validated());

            return $this->successResponse(new NeighborhoodResource($neighborhood), 'Neighborhood created successfully', Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return $this->errorResponse('Error creating neighborhood', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/neighborhoods/{id}",
     *     operationId="showNeighborhood",
     *     tags={"Neighborhoods"},
     *     summary="Show neighborhood",
     *     description="Get a specific neighborhood",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/NeighborhoodResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $neighborhood = $this->neighborhoodService->show($id);

            return $this->successResponse(new NeighborhoodResource($neighborhood), 'Neighborhood retrieved successfully');
        } catch (Throwable $e) {
            return $this->errorResponse('Neighborhood not found', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/neighborhoods/{id}",
     *     operationId="updateNeighborhood",
     *     tags={"Neighborhoods"},
     *     summary="Update neighborhood",
     *     description="Update a specific neighborhood",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/NeighborhoodRequest")),
     *
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/NeighborhoodResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(NeighborhoodRequest $request, int $neighborhood_id): JsonResponse
    {
        try {
            $neighborhood = $this->neighborhoodService->update($neighborhood_id, $request->validated());

            return $this->successResponse(new NeighborhoodResource($neighborhood), 'Neighborhood updated successfully');
        } catch (Throwable $e) {
            return $this->errorResponse('Error updating neighborhood', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/neighborhoods/{id}",
     *     operationId="deleteNeighborhood",
     *     tags={"Neighborhoods"},
     *     summary="Delete neighborhood",
     *     description="Delete a specific neighborhood",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(int $neighborhood_id): JsonResponse
    {
        try {
            $this->neighborhoodService->destroy($neighborhood_id);

            return $this->noContentResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable $e) {
            return $this->errorResponse('Error deleting neighborhood', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
