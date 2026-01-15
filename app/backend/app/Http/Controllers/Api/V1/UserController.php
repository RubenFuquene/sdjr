<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserIndexRequest;
use App\Http\Requests\Api\V1\UserRequest;
use App\Http\Requests\Api\V1\UserStatusRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints of Users"
 * )
 */
class UserController extends Controller
{
    use ApiResponseTrait;

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     operationId="indexUsers",
     *     tags={"Users"},
     *     summary="List users",
     *     description="Get paginated list of users. Permite filtrar por nombre (name), email, estado (status) y cantidad por página (per_page).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false, description="Filtrar por nombre de usuario (texto parcial)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="email", in="query", required=false, description="Filtrar por email de usuario", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filtrar por estado: 1=activos, 0=inactivos", @OA\Schema(type="string", enum={"1","0"}, default="1")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", example=15)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(type="object")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(UserIndexRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $users = $this->userService->getPaginated($filters, $perPage);
            $resource = UserResource::collection($users);

            return $this->paginatedResponse($users, $resource, 'Users retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing users', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error listing users', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     operationId="storeUser",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Creates a new user.",
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
     *         description="User created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->create($request->validated());

            return $this->successResponse(new UserResource($user), 'User created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating user', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating users', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{user}",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     summary="Get user by ID",
     *     description="Returns a single user with roles and permissions.",
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
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(int $user_id): JsonResponse
    {
        try {
            $user = $this->userService->find($user_id);
            if (! $user) {
                return $this->errorResponse('user not found', 404);
            }

            return $this->successResponse(new UserResource($user), 'User retrieved successfully', Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Error retrieving user', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error retrieving users', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{user}",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     summary="Update user",
     *     description="Updates a user.",
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
     *         description="User updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UserRequest $request, int $user_id): JsonResponse
    {
        try {
            $updatedUser = $this->userService->update($user_id, $request->validated());

            return $this->successResponse(new UserResource($updatedUser), 'User updated successfully', Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Error updating user', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating users', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{user}",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     summary="Delete user",
     *     description="Deletes a user.",
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
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(int $user_id): JsonResponse
    {
        try {
            $this->userService->delete($user_id);

            return $this->noContentResponse();
        } catch (\Throwable $e) {
            Log::error('Error deleting user', ['id' => $user?->id, 'error' => $e->getMessage()]);

            return $this->errorResponse('Error deleting users', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/users/{user}/status",
     *     operationId="updateUserStatus",
     *     tags={"Users"},
     *     summary="Activate or inactivate a user",
     *     description="Updates the status (active/inactive) of a user.",
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
     *         @OA\JsonContent(ref="#/components/schemas/UserStatusRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User status updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function updateStatus(UserStatusRequest $request, int $user_id): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateStatus($user_id, $request->validated('status'));

            return $this->successResponse(new UserResource($updatedUser), 'User status updated successfully');
        } catch (\Throwable $e) {
            Log::error('Error updating user status', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating user status', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/administrators",
     *     operationId="getAdministrators",
     *     tags={"Users"},
     *     summary="Get list of administrator users",
     *     description="Returns a list of users with the administrator role.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/UserResource"))
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function administrators(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $users = User::with('roles')->get()->filter(
                fn ($user) => $user->roles->whereIn('name', ['superadmin', 'admin'])->toArray()
            );

            return $this->successResponse(UserResource::collection($users), 'Administrators retrieved successfully', Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Error listing administrators', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error listing administrators', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }
}
