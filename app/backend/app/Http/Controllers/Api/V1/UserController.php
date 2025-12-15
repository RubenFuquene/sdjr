<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserRequest;
use App\Http\Requests\Api\V1\UserIndexRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\Api\V1\UserStatusRequest;

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
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     summary="Get list of users",
     *     description="Returns list of users with roles and permissions.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/UserResource"))
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(UserIndexRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = $request->validatedPerPage();
            $users = $this->userService->getPaginated($perPage);
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
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
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(int $user_id): JsonResponse
    {
        try {
            $user = $this->userService->find($user_id);
            if (!$user) {
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
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
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
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"0","1"}, description="User status: 1=active, 0=inactive")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
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
            \Log::error('Error updating user status', ['id' => $user?->id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error updating user status', 500);
        }
    }
}