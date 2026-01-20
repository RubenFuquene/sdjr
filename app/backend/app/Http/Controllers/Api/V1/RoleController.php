<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Role;
use App\Models\User;
use App\Constants\Constant;
use App\Services\RoleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RoleResource;
use App\Http\Requests\Api\V1\StoreRoleRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Api\V1\UpdateRoleRequest;
use App\Http\Requests\Api\V1\PatchRoleStatusRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Api\V1\RoleAssignPermissionRequest;
use App\Http\Requests\Api\V1\UserAssignRolePermissionRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="API Endpoints of Roles"
 * )
 * @OA\Tag(
 *     name="Permissions",
 *     description="API Endpoints of Permissions"
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints of Users"
 * )
 */
class RoleController extends Controller
{
    use ApiResponseTrait;

    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles",
     *     operationId="indexRoles",
     *     tags={"Roles"},
     *     summary="List roles",
     *     description="Get paginated list of roles. Permite filtrar por name, description, permission y status.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false, description="Filter by name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="description", in="query", required=false, description="Filter by description", @OA\Schema(type="string")),
     *     @OA\Parameter(name="permission", in="query", required=false, description="Filter by permission", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page", @OA\Schema(type="integer", example=15)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filters = [
                'name' => request('name'),
                'description' => request('description'),
                'permission' => request('permission'),
                'per_page' => request('per_page', Constant::DEFAULT_PER_PAGE),
                'q' => request('q'),
            ];
            $roles = $this->roleService->getPaginatedWithPermissionsAndUserCount($filters);
            $resource = RoleResource::collection($roles);

            return $this->paginatedResponse($roles, $resource, 'Roles retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing roles', ['error' => $e->getMessage().' Line: '.$e->getLine()]);

            return $this->errorResponse('Error listing roles', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/roles",
     *     operationId="storeRole",
     *     tags={"Roles"},
     *     summary="Create a new role",
     *     description="Creates a new role and assigns permissions.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreRoleRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->createRole(
                $request->validated('name'),
                $request->validated('description'),
                $request->validated('permissions', [])
            );

            return $this->successResponse($role, 'Role created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating role', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating role', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{user}/assign-roles-permissions",
     *     operationId="assignRolesPermissionsToUser",
     *     tags={"Users"},
     *     summary="Assign roles and permissions to user",
     *     description="Assigns roles and permissions to a user.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(type="object")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Roles and permissions assigned successfully",
     *
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Roles and permissions assigned successfully"))
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function assignRolesPermissions(UserAssignRolePermissionRequest $request, User $user): JsonResponse
    {
        try {
            $this->roleService->assignToUser(
                $user,
                $request->validated('roles', []),
                $request->validated('permissions', []),
                $request->boolean('sync', true)
            );

            return $this->successResponse(null, 'Roles and permissions assigned successfully', Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Error assigning roles/permissions', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error assigning roles/permissions', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/roles/{role}/assign-permissions",
     *     operationId="assignPermissionsToRole",
     *     tags={"Roles"},
     *     summary="Assign permissions to a role",
     *     description="Assigns permissions to a role.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(type="object")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permissions assigned successfully",
     *
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Permissions assigned successfully"))
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function assignPermissionsToRole(RoleAssignPermissionRequest $request, Role $role): JsonResponse
    {
        try {
            $this->roleService->assignPermissionsToRole($role, $request->validated('permissions'), $request->boolean('sync', true));

            return $this->successResponse(null, 'Permissions assigned successfully', 200);
        } catch (\Throwable $e) {
            Log::error('Error assigning permissions to role', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error assigning permissions to role', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}",
     *     operationId="getRoleDetail",
     *     tags={"Roles"},
     *     summary="Get role detail",
     *     description="Returns the detail of a role including its permissions and user count.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
     *
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $role = Role::with('permissions')->find($id);
            if (! $role) {
                return $this->errorResponse('Role not found', 404);
            }

            return $this->successResponse(new RoleResource($role), 'Role retrieved successfully', 200);
        } catch (\Throwable $e) {
            Log::error('Error retrieving role', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error retrieving role', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/roles/{id}",
     *     operationId="updateRole",
     *     tags={"Roles"},
     *     summary="Update a role",
     *     description="Updates the specified role.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateRoleRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
     *
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        try {
            $role = Role::find($id);
            if (! $role) {
                return $this->errorResponse('Role not found', 404);
            }
            $role->update($request->validated());
            if ($request->has('permissions')) {
                $role->syncPermissions($request->validated('permissions'));
            }

            return $this->successResponse(new RoleResource($role->fresh('permissions')), 'Role updated successfully', 200);
        } catch (\Throwable $e) {
            Log::error('Error updating role', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating role', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/roles/{id}/status",
     *     operationId="patchRoleStatus",
     *     tags={"Roles"},
     *     summary="Update role status",
     *     description="Updates the status of a role (active/inactive).",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=true,
     *         description="Role status (1=active, 0=inactive)",
     *         @OA\Schema(type="integer", enum={1,0})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function patchStatus(PatchRoleStatusRequest $request, int $id): JsonResponse
    {
        try {
            $role = $this->roleService->updateStatus($id, (int) $request->validated('status'));
            return $this->successResponse(new RoleResource($role), 'Role status updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Role not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error updating role status', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error updating role status', 500, ['exception' => $e->getMessage()]);
        }
    }
}
