<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeletePriorityTypeRequest;
use App\Http\Requests\Api\V1\IndexPriorityTypeRequest;
use App\Http\Requests\Api\V1\ShowPriorityTypeRequest;
use App\Http\Requests\Api\V1\StorePriorityTypeRequest;
use App\Http\Requests\Api\V1\UpdatePriorityTypeRequest;
use App\Http\Resources\Api\V1\PriorityTypeResource;
use App\Services\PriorityTypeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="PriorityTypes",
 *     description="API Endpoints of Priority Types"
 * )
 */
class PriorityTypeController extends Controller
{
    use ApiResponseTrait;

    protected PriorityTypeService $priorityTypeService;

    public function __construct(PriorityTypeService $priorityTypeService)
    {
        $this->priorityTypeService = $priorityTypeService;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/priority-types",
     *      operationId="getPriorityTypesList",
     *      tags={"PriorityTypes"},
     *      summary="Get list of priority types",
     *      description="Returns list of priority types",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          required=false,
     *          description="Items per page",
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=false,
     *          description="Filter by name",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="code",
     *          in="query",
     *          required=false,
     *          description="Filter by code",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          required=false,
     *          description="Filter by status (1=active, 0=inactive)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PriorityTypeResource")),
     *              @OA\Property(property="meta", type="object"),
     *              @OA\Property(property="links", type="object")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(IndexPriorityTypeRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filters = $request->only(['name', 'code', 'status']);
            $perPage = $request->validatedPerPage();
            $priorityTypes = $this->priorityTypeService->getPaginated($filters, $perPage);
            $resource = PriorityTypeResource::collection($priorityTypes);

            return $this->paginatedResponse($priorityTypes, $resource, 'Priority types retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving priority types', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created priority type.
     *
     * @OA\Post(
     *      path="/api/v1/priority-types",
     *      operationId="storePriorityType",
     *      tags={"PriorityTypes"},
     *      summary="Store new priority type",
     *      description="Returns created priority type data",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StorePriorityTypeRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/PriorityTypeResource")
     *      ),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(StorePriorityTypeRequest $request): PriorityTypeResource|JsonResponse
    {
        try {
            $priorityType = $this->priorityTypeService->create($request->validated());

            return $this->successResponse(new PriorityTypeResource($priorityType), 'Priority type created successfully', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error creating priority type', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified priority type.
     *
     * @OA\Get(
     *      path="/api/v1/priority-types/{id}",
     *      operationId="showPriorityType",
     *      tags={"PriorityTypes"},
     *      summary="Get priority type information",
     *      description="Returns priority type data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Priority type ID",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/PriorityTypeResource")
     *      ),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(ShowPriorityTypeRequest $request, string $id): PriorityTypeResource|JsonResponse
    {
        try {
            $priorityType = $this->priorityTypeService->find($id);
            if (! $priorityType) {
                return $this->errorResponse('Priority type not found', 404);
            }

            return $this->successResponse(new PriorityTypeResource($priorityType), 'Priority type retrieved successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving priority type', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified priority type.
     *
     * @OA\Put(
     *      path="/api/v1/priority-types/{id}",
     *      operationId="updatePriorityType",
     *      tags={"PriorityTypes"},
     *      summary="Update existing priority type",
     *      description="Returns updated priority type data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Priority type ID",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdatePriorityTypeRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/PriorityTypeResource")
     *      ),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(UpdatePriorityTypeRequest $request, string $id): PriorityTypeResource|JsonResponse
    {
        try {
            $priorityType = $this->priorityTypeService->find($id);
            if (! $priorityType) {
                return $this->errorResponse('Priority type not found', 404);
            }
            $updatedPriorityType = $this->priorityTypeService->update($priorityType, $request->validated());

            return $this->successResponse(new PriorityTypeResource($updatedPriorityType), 'Priority type updated successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error updating priority type', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified priority type.
     *
     * @OA\Delete(
     *      path="/api/v1/priority-types/{id}",
     *      operationId="deletePriorityType",
     *      tags={"PriorityTypes"},
     *      summary="Delete existing priority type",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Priority type ID",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=204, description="No Content"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(DeletePriorityTypeRequest $request, string $id): JsonResponse
    {
        try {
            $priorityType = $this->priorityTypeService->find($id);
            if (! $priorityType) {
                return $this->errorResponse('Priority type not found', 404);
            }
            $this->priorityTypeService->delete($priorityType);

            return $this->noContentResponse();
        } catch (\Throwable $e) {
            return $this->errorResponse('Error deleting priority type', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}
