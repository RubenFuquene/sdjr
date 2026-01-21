<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\CommerceBranchService;
use App\Http\Resources\CommerceBranchResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Api\V1\ShowCommerceBranchRequest;
use App\Http\Requests\Api\V1\IndexCommerceBranchRequest;
use App\Http\Requests\Api\V1\StoreCommerceBranchRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Api\V1\DeleteCommerceBranchRequest;
use App\Http\Requests\Api\V1\UpdateCommerceBranchRequest;

/**
 * @OA\Tag(
 *     name="Commerce Branches",
 *     description="API Endpoints for Commerce Branches"
 * )
 */
class CommerceBranchController extends Controller
{
    use ApiResponseTrait;

    private CommerceBranchService $commerceService;

    public function __construct(CommerceBranchService $service)
    {
        $this->commerceService = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerce-branches",
     *     operationId="getCommerceBranches",
     *     tags={"Commerce Branches"},
     *     summary="List all commerce branches",
     *     description="Returns paginated list of all commerce branches.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="name", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="latitude", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="longitude", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="email", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="address", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="phone", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object",
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommerceBranchResource")),
     *         @OA\Property(property="meta", type="object"),
     *         @OA\Property(property="links", type="object")
     *     )),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexCommerceBranchRequest $request): JsonResponse
    {
        $filters = $request->validatedFilters();
        $perPage = $request->validatedPerPage();
        $branches = $this->commerceService->getPaginated($filters, $perPage);
        $resource = CommerceBranchResource::collection($branches);
        return $this->paginatedResponse($branches, $resource, 'Branches retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerce-branches",
     *     operationId="storeCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Create a new branch",
     *     description="Creates a new commerce branch.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreCommerceBranchRequest")),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/CommerceBranchResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreCommerceBranchRequest $request): JsonResponse
    {
        try {
            $branch = $this->commerceService->store($request->validated());
            return $this->successResponse(new CommerceBranchResource($branch), 'Branch created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating branch', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error creating branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerce-branches/{id}",
     *     operationId="showCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Show a branch",
     *     description="Returns a single commerce branch.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/CommerceBranchResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(ShowCommerceBranchRequest $request, int $id): JsonResponse
    {
        try {
            $branch = $this->commerceService->show($id);
            return $this->successResponse(new CommerceBranchResource($branch), 'Branch retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Branch not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error retrieving branch', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error retrieving branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/commerce-branches/{id}",
     *     operationId="updateCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Update a branch",
     *     description="Updates a commerce branch.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateCommerceBranchRequest")),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/CommerceBranchResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(UpdateCommerceBranchRequest $request, int $id): JsonResponse
    {
        try {
            $branch = $this->commerceService->update($id, $request->validated());
            return $this->successResponse(new CommerceBranchResource($branch), 'Branch updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Branch not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error updating branch', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error updating branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/commerce-branches/{id}",
     *     operationId="deleteCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Delete a branch",
     *     description="Deletes a commerce branch (soft delete).",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(DeleteCommerceBranchRequest $request, int $id): JsonResponse
    {
        try {
            $this->commerceService->destroy($id);
            return $this->successResponse(null, 'Branch deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Branch not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error deleting branch', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error deleting branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }
}
