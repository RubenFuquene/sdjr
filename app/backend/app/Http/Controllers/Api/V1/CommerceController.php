<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CommerceRequest;
use App\Http\Resources\Api\V1\CommerceResource;
use App\Services\CommerceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponseTrait;

/**
 * @OA\Tag(
 *     name="Commerces",
 *     description="API Endpoints of Commerces"
 * )
 */
class CommerceController extends Controller
{
    use ApiResponseTrait;
    private CommerceService $commerceService;

    public function __construct(CommerceService $commerceService)
    {
        $this->commerceService = $commerceService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerces",
     *     operationId="getCommercesList",
     *     tags={"Commerces"},
     *     summary="Get list of commerces",
     *     description="Returns paginated list of commerces.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommerceResource")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = request('per_page', 15);
            $commerces = $this->commerceService->paginate((int)$perPage);
            $resource = CommerceResource::collection($commerces);
            return $this->paginatedResponse($commerces, $resource, 'Commerces retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing commerces', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error listing commerces', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerces",
     *     operationId="storeCommerce",
     *     tags={"Commerces"},
     *     summary="Create a new commerce",
     *     description="Creates a new commerce.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CommerceRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Commerce created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(CommerceRequest $request): JsonResponse
    {
        try {
            $commerce = $this->commerceService->store($request->validated());
            return $this->successResponse(new CommerceResource($commerce), 'Commerce created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating commerce', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error creating commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerces/{id}",
     *     operationId="showCommerce",
     *     tags={"Commerces"},
     *     summary="Get a commerce",
     *     description="Returns a single commerce.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(int $commerce_id): JsonResponse
    {
        try {
            return $this->successResponse(new CommerceResource($this->commerceService->show($commerce_id)), 'Commerce retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error showing commerce', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error showing commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/commerces/{id}",
     *     operationId="updateCommerce",
     *     tags={"Commerces"},
     *     summary="Update a commerce",
     *     description="Updates a commerce.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CommerceRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commerce updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function update(CommerceRequest $request, int $commerce_id): JsonResponse
    {
        try {
            $commerce = $this->commerceService->update($commerce_id, $request->validated());
            return $this->successResponse(new CommerceResource($commerce), 'Commerce updated successfully');
        } catch (\Throwable $e) {
            Log::error('Error updating commerce', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error updating commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/commerces/{id}",
     *     operationId="deleteCommerce",
     *     tags={"Commerces"},
     *     summary="Delete a commerce",
     *     description="Deletes a commerce (soft delete).",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Commerce deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(int $commerce_id): JsonResponse
    {
        try {
            $this->commerceService->delete($commerce_id);
            return $this->successResponse(null, 'Commerce deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            Log::error('Error deleting commerce', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error deleting commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }
}
