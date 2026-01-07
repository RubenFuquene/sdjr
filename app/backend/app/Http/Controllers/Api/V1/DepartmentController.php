<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteDepartmentRequest;
use App\Http\Requests\Api\V1\DepartmentRequest;
use App\Http\Requests\Api\V1\IndexDepartmentRequest;
use App\Http\Requests\Api\V1\ShowDepartmentRequest;
use App\Http\Resources\Api\V1\DepartmentResource;
use App\Services\DepartmentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Departments",
 *     description="API Endpoints of Departments"
 * )
 */
class DepartmentController extends Controller
{
    use ApiResponseTrait;

    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/departments",
     *      operationId="getDepartmentsList",
     *      tags={"Departments"},
     *      summary="Get list of departments",
     *      description="Returns list of departments",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          required=false,
     *          description="Items per page",
     *
     *          @OA\Schema(ref="#/components/schemas/IndexDepartmentRequest", property="per_page")
     *      ),
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=false,
     *          description="Filter by name",
     *
     *          @OA\Schema(ref="#/components/schemas/IndexDepartmentRequest", property="name")
     *      ),
     *
     *      @OA\Parameter(
     *          name="code",
     *          in="query",
     *          required=false,
     *          description="Filter by code",
     *
     *          @OA\Schema(ref="#/components/schemas/IndexDepartmentRequest", property="code")
     *      ),
     *
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          required=false,
     *          description="Filter by status (1=active, 0=inactive)",
     *
     *          @OA\Schema(ref="#/components/schemas/IndexDepartmentRequest", property="status")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DepartmentResource")),
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
    public function index(IndexDepartmentRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filters = $request->only(['name', 'code', 'status']);
            $perPage = $request->validatedPerPage();
            $departments = $this->departmentService->getPaginated($filters, $perPage);
            $resource = DepartmentResource::collection($departments);

            return $this->paginatedResponse($departments, $resource, 'Departments retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving departments', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created department.
     *
     * @OA\Post(
     *      path="/api/v1/departments",
     *      operationId="storeDepartment",
     *      tags={"Departments"},
     *      summary="Store new department",
     *      description="Returns created department data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentResource")
     *      ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(DepartmentRequest $request): DepartmentResource|JsonResponse
    {
        try {
            $department = $this->departmentService->create($request->validated());

            return $this->successResponse(new DepartmentResource($department), 'Department created successfully', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error creating department', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified department.
     *
     * @OA\Get(
     *      path="/api/v1/departments/{id}",
     *      operationId="showDepartment",
     *      tags={"Departments"},
     *      summary="Get department information",
     *      description="Returns department data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Department ID",
     *
     *          @OA\Schema(ref="#/components/schemas/ShowDepartmentRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentResource")
     *      ),
     *
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(ShowDepartmentRequest $request, string $id): DepartmentResource|JsonResponse
    {
        try {
            $department = $this->departmentService->find($id);

            return $this->successResponse(new DepartmentResource($department), 'Department retrieved successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving department', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified department.
     *
     * @OA\Put(
     *      path="/api/v1/departments/{id}",
     *      operationId="updateDepartment",
     *      tags={"Departments"},
     *      summary="Update existing department",
     *      description="Returns updated department data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Department ID",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentResource")
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
    public function update(DepartmentRequest $request, string $id): DepartmentResource|JsonResponse
    {
        try {
            $department = $this->departmentService->find($id);
            if (! $department) {
                return $this->errorResponse('Department not found', 404);
            }
            $updatedDepartment = $this->departmentService->update($department, $request->validated());

            return $this->successResponse(new DepartmentResource($updatedDepartment), 'Department updated successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error updating department', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified department.
     *
     * @OA\Delete(
     *      path="/api/v1/departments/{id}",
     *      operationId="deleteDepartment",
     *      tags={"Departments"},
     *      summary="Delete existing department",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Department ID",
     *
     *          @OA\Schema(ref="#/components/schemas/DeleteDepartmentRequest")
     *      ),
     *
     *      @OA\Response(response=204, description="No Content"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(DeleteDepartmentRequest $request, string $id): JsonResponse
    {
        try {
            $department = $this->departmentService->find($id);
            if (! $department) {
                return $this->errorResponse('Department not found', 404);
            }
            $this->departmentService->delete($department);

            return $this->noContentResponse();
        } catch (\Throwable $e) {
            return $this->errorResponse('Error deleting department', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}
