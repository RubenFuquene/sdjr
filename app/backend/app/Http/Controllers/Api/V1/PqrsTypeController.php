<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeletePqrsTypeRequest;
use App\Http\Requests\Api\V1\IndexPqrsTypeRequest;
use App\Http\Requests\Api\V1\ShowPqrsTypeRequest;
use App\Http\Requests\Api\V1\StorePqrsTypeRequest;
use App\Http\Requests\Api\V1\UpdatePqrsTypeRequest;
use App\Http\Resources\Api\V1\PqrsTypeResource;
use App\Services\PqrsTypeService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="PqrsTypes",
 *     description="API Endpoints of PQRS Types"
 * )
 */
class PqrsTypeController extends Controller
{
    use ApiResponseTrait;

    protected PqrsTypeService $service;

    public function __construct(PqrsTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pqrs-types",
     *     operationId="indexPqrsTypes",
     *     tags={"PqrsTypes"},
     *     summary="List PQRS Types",
     *     description="Get paginated list of PQRS Types. Permite filtrar por status, name y code.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="name", in="query", required=false, description="Filter by name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="code", in="query", required=false, description="Filter by code", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page", @OA\Schema(type="integer", example=15)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexPqrsTypeRequest $request): JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $pqrs = $this->service->getPaginated($filters, $perPage);
            $resource = PqrsTypeResource::collection($pqrs);

            return $this->paginatedResponse($pqrs, $resource, 'Pqrs Types retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Error listing Pqrs Types', 500);
        }
    }

    /**
     * Create a new Pqrs Type.
     *
     * @OA\Post(
     *     path="/api/v1/pqrs-types",
     *     operationId="storePqrsType",
     *     tags={"PqrsTypes"},
     *     summary="Create Pqrs Type",
     *     description="Creates a new Pqrs Type",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", maxLength=100, example="Queja"),
     *             @OA\Property(property="code", type="string", maxLength=20, example="Q"),
     *             @OA\Property(property="status", type="string", maxLength=1, example="1")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PqrsTypeResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StorePqrsTypeRequest $request): JsonResponse
    {
        try {
            $pqrsType = $this->service->store($request->validated());

            return $this->successResponse(new PqrsTypeResource($pqrsType), 'Pqrs Type created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse('Error creating Pqrs Type', 500);
        }
    }

    /**
     * Get a single Pqrs Type.
     *
     * @OA\Get(
     *     path="/api/v1/pqrs-types/{id}",
     *     operationId="showPqrsType",
     *     tags={"PqrsTypes"},
     *     summary="Show Pqrs Type",
     *     description="Returns a single Pqrs Type",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Pqrs Type ID", @OA\Schema(ref="#/components/schemas/ShowPqrsTypeRequest")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PqrsTypeResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(ShowPqrsTypeRequest $request, int $id): JsonResponse
    {
        try {
            $pqrsType = $this->service->show($id);

            return $this->successResponse(new PqrsTypeResource($pqrsType), 'Pqrs Type retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Pqrs Type not found', 404);
        }
    }

    /**
     * Update a Pqrs Type.
     *
     * @OA\Put(
     *     path="/api/v1/pqrs-types/{id}",
     *     operationId="updatePqrsType",
     *     tags={"PqrsTypes"},
     *     summary="Update Pqrs Type",
     *     description="Updates an existing Pqrs Type",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Pqrs Type ID", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=100, example="Queja Actualizada"),
     *             @OA\Property(property="code", type="string", maxLength=20, example="QA"),
     *             @OA\Property(property="status", type="string", maxLength=1, example="1")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PqrsTypeResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(UpdatePqrsTypeRequest $request, int $id): JsonResponse
    {
        try {
            $pqrsType = $this->service->update($id, $request->validated());

            return $this->successResponse(new PqrsTypeResource($pqrsType), 'Pqrs Type updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Error updating Pqrs Type', 500);
        }
    }

    /**
     * Delete a Pqrs Type.
     *
     * @OA\Delete(
     *     path="/api/v1/pqrs-types/{id}",
     *     operationId="deletePqrsType",
     *     tags={"PqrsTypes"},
     *     summary="Delete Pqrs Type",
     *     description="Deletes a Pqrs Type",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Pqrs Type ID", @OA\Schema(ref="#/components/schemas/DeletePqrsTypeRequest")),
     *
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(DeletePqrsTypeRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->destroy($id);

            return $this->noContentResponse(null, 204);
        } catch (Exception $e) {
            return $this->errorResponse('Error deleting Pqrs Type', 500);
        }
    }
}
