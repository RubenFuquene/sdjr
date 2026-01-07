<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SupportStatusStoreRequest;
use App\Http\Requests\Api\V1\SupportStatusUpdateRequest;
use App\Http\Resources\Api\V1\SupportStatusResource;
use App\Services\SupportStatusService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SupportStatusController extends Controller
{
    use ApiResponseTrait;

    private SupportStatusService $supportStatusService;

    public function __construct(SupportStatusService $supportStatusService)
    {
        $this->supportStatusService = $supportStatusService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/support-statuses",
     *     operationId="indexSupportStatuses",
     *     tags={"SupportStatuses"},
     *     summary="List support statuses",
     *     description="Get paginated list of support statuses. Permite filtrar por name, code, color y status.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false, description="Filter by name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="code", in="query", required=false, description="Filter by code", @OA\Schema(type="string")),
     *     @OA\Parameter(name="color", in="query", required=false, description="Filter by color", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page", @OA\Schema(type="integer", example=15)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name', 'code', 'color', 'status']);
            $perPage = (int) $request->get('per_page', 15);
            $statuses = $this->supportStatusService->getPaginated($filters, $perPage);
            $resource = SupportStatusResource::collection($statuses);

            return $this->paginatedResponse($statuses, $resource, 'Support statuses retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing support statuses', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error listing support statuses', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/support-statuses",
     *     operationId="storeSupportStatus",
     *     tags={"SupportStatuses"},
     *     summary="Create a new support status",
     *     description="Creates a new support status.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SupportStatusStoreRequest")),
     *
     *     @OA\Response(response=201, description="Support status created successfully", @OA\JsonContent(ref="#/components/schemas/SupportStatusResource")),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(SupportStatusStoreRequest $request): JsonResponse
    {
        try {
            $status = $this->supportStatusService->store($request->validated());

            return $this->successResponse(new SupportStatusResource($status), 'Support status created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating support status', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating support status', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/support-statuses/{id}",
     *     operationId="getSupportStatusDetail",
     *     tags={"SupportStatuses"},
     *     summary="Get support status detail",
     *     description="Returns the detail of a support status.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/SupportStatusResource")),
     *     @OA\Response(response=404, description="Support status not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $status = $this->supportStatusService->find($id);

            return $this->successResponse(new SupportStatusResource($status), 'Support status retrieved successfully', Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Support status not found', Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Error retrieving support status', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error retrieving support status', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/support-statuses/{id}",
     *     operationId="updateSupportStatus",
     *     tags={"SupportStatuses"},
     *     summary="Update a support status",
     *     description="Updates the specified support status.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SupportStatusUpdateRequest")),
     *
     *     @OA\Response(response=200, description="Support status updated successfully", @OA\JsonContent(ref="#/components/schemas/SupportStatusResource")),
     *     @OA\Response(response=404, description="Support status not found"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(SupportStatusUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $status = $this->supportStatusService->update($id, $request->validated());

            return $this->successResponse(new SupportStatusResource($status), 'Support status updated successfully', Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Support status not found', Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Error updating support status', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating support status', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/support-statuses/{id}",
     *     operationId="deleteSupportStatus",
     *     tags={"SupportStatuses"},
     *     summary="Delete a support status",
     *     description="Deletes the specified support status (soft delete).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="Support status deleted successfully"),
     *     @OA\Response(response=404, description="Support status not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->supportStatusService->delete($id);

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Support status not found', Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Error deleting support status', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error deleting support status', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }
}
