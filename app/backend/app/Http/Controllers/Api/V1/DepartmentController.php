<?php

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Services\DepartmentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DepartmentRequest;
use App\Http\Resources\Api\V1\DepartmentResource;
use App\Http\Requests\Api\V1\ShowDepartmentRequest;
use App\Http\Requests\Api\V1\IndexDepartmentRequest;
use App\Http\Requests\Api\V1\DeleteDepartmentRequest;
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
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentResource")
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
    public function index(IndexDepartmentRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = $request->validatedPerPage();
            $status = $request->validatedStatus();
            $departments = $this->departmentService->getPaginated($perPage, $status);
            $resource = DepartmentResource::collection($departments);

            return $this->paginatedResponse($departments, $resource, 'Departments retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving departments', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/departments",
     *      operationId="storeDepartment",
     *      tags={"Departments"},
     *      summary="Store new department",
     *      description="Returns department data",
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
     * @return DepartmentResource
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
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/departments/{id}",
     *      operationId="getDepartmentById",
     *      tags={"Departments"},
     *      summary="Get department information",
     *      description="Returns department data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Department id",
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
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentResource")
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
     * Update the specified resource in storage.
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
     *          description="Department id",
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
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DepartmentResource")
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
     * Remove the specified resource from storage.
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
     *          description="Department id",
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
     *              @OA\Property(property="message", type="string", example="Department deleted successfully")
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
