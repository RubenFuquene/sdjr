<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\RoleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RoleResource;
use App\Http\Requests\Api\V1\RoleStoreRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Api\V1\RoleUpdateRequest;
use App\Http\Requests\Api\V1\PermissionStoreRequest;
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
     *     operationId="getRolesList",
     *     tags={"Roles"},
     *     summary="Get list of roles",
     *     description="Returns a paginated list of roles with permissions and user count.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RoleResource")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *
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
                'per_page' => request('per_page', 15),
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
     *         @OA\JsonContent(ref="#/components/schemas/RoleStoreRequest")
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
    public function store(RoleStoreRequest $request): JsonResponse
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
     *         @OA\JsonContent(ref="#/components/schemas/UserAssignRolePermissionRequest")
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
     *         @OA\JsonContent(ref="#/components/schemas/RoleAssignPermissionRequest")
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
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
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
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RoleUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/RoleResource")
     *     ),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(RoleUpdateRequest $request, int $id): JsonResponse
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
}
