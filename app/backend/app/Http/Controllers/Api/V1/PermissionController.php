<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePermissionRequest;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Services\RoleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="API Endpoints of Permissions"
 * )
 */
class PermissionController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/api/v1/permissions",
     *     operationId="getPermissions",
     *     tags={"Permissions"},
     *     summary="Get all permissions",
     *     description="Returns a list of all permissions created in the system.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PermissionResource")),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $permissions = Permission::all();
            $resource = PermissionResource::collection($permissions);

            return $this->successResponse($resource, 'Permissions retrieved successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving permissions', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/permissions",
     *     operationId="storePermission",
     *     tags={"Permissions"},
     *     summary="Create a new permission",
     *     description="Creates a new permission.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(type="object")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Permission created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="users.create"),
     *             @OA\Property(property="description", type="string", example="Permite crear usuarios"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-15T12:34:56Z")
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        try {
            $permission = app(RoleService::class)->createPermission(
                $request->validated('name'),
                $request->validated('description')
            );

            return $this->successResponse($permission, 'Permission created successfully', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error creating permission', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}

