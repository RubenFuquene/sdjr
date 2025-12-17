<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Throwable;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\LegalRepresentativeService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Api\V1\LegalRepresentativeResource;
use App\Http\Requests\Api\V1\LegalRepresentativeRequest;

/**
 * @OA\Tag(
 *     name="LegalRepresentatives",
 *     description="API Endpoints for Legal Representatives"
 * )
 */
class LegalRepresentativeController extends Controller
{
    use ApiResponseTrait;
    private LegalRepresentativeService $legalRepresentativeService;

    public function __construct(LegalRepresentativeService $service)
    {
        $this->legalRepresentativeService = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/legal-representatives",
     *     summary="List legal representatives",
     *     tags={"LegalRepresentatives"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(type="object", @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LegalRepresentativeResource")))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 15);
            $legalRepresentatives = $this->legalRepresentativeService->paginate($perPage);
            return $this->paginatedResponse($legalRepresentatives, LegalRepresentativeResource::collection($legalRepresentatives), 'Legal representatives retrieved successfully');
        } catch (Throwable $e) {
            Log::error('Error retrieving legal representatives', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error retrieving legal representatives', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/legal-representatives",
     *     summary="Create legal representative",
     *     tags={"LegalRepresentatives"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LegalRepresentativeRequest")),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/LegalRepresentativeResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(LegalRepresentativeRequest $request): JsonResponse
    {
        try {
            $legalRepresentative = $this->legalRepresentativeService->store($request->validated());
            return $this->successResponse(new LegalRepresentativeResource($legalRepresentative), 'Legal representative created successfully', Response::HTTP_CREATED);            
        } catch (Throwable $e) {
            Log::error('Error creating legal representative', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error creating legal representative', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/legal-representatives/{id}",
     *     summary="Show legal representative",
     *     tags={"LegalRepresentatives"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(ref="#/components/schemas/LegalRepresentativeResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $legalRepresentative = $this->legalRepresentativeService->show($id);
            return $this->successResponse(new LegalRepresentativeResource($legalRepresentative), 'Legal representative retrieved successfully', Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Error retrieving legal representative', ['error' => $e->getMessage()]);
            return $this->errorResponse('Legal representative not found', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/legal-representatives/{id}",
     *     summary="Update legal representative",
     *     tags={"LegalRepresentatives"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LegalRepresentativeRequest")),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/LegalRepresentativeResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(LegalRepresentativeRequest $request, int $id): JsonResponse
    {
        try {
            $legalRepresentative = $this->legalRepresentativeService->update($id, $request->validated());
            return $this->successResponse(new LegalRepresentativeResource($legalRepresentative), 'Legal representative updated successfully', Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Error updating legal representative', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error updating legal representative', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/legal-representatives/{id}",
     *     summary="Delete legal representative",
     *     tags={"LegalRepresentatives"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->legalRepresentativeService->destroy($id);
            return $this->successResponse(null, 'Legal representative deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (Throwable $e) {
            Log::error('Error deleting legal representative', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error deleting legal representative', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
