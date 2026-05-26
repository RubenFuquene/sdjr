<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AssignCommerceBranchUserRequest;
use App\Http\Requests\Api\V1\IndexCommerceBranchUserRequest;
use App\Http\Requests\Api\V1\RemoveCommerceBranchUserRequest;
use App\Http\Requests\Api\V1\ShowCommerceBranchUsersRequest;
use App\Http\Requests\Api\V1\StoreCommerceBranchUserRequest;
use App\Http\Resources\Api\V1\CommerceBranchUserResource;
use App\Services\CommerceBranchUserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Commerce Branch Users",
 *     description="API Endpoints for Commerce Branch Leader Management"
 * )
 */
class CommerceBranchUserController extends Controller
{
    use ApiResponseTrait;

    private CommerceBranchUserService $commerceBranchUserService;

    public function __construct(CommerceBranchUserService $commerceBranchUserService)
    {
        $this->commerceBranchUserService = $commerceBranchUserService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerce-branch-users",
     *     operationId="indexCommerceBranchUsers",
     *     tags={"Commerce Branch Users"},
     *     summary="List branch leaders for a commerce",
     *     description="Get paginated list of all branch leaders assigned to a specific commerce",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="commerce_id", in="query", required=true, description="Commerce ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", minimum=1, maximum=100, example=15)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Commerce not found"),
     *     @OA\Response(response=429, description="Too Many Requests")
     * )
     */
    public function index(IndexCommerceBranchUserRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $commerceId = (int) $request->input('commerce_id');
            $perPage = $request->validatedPerPage();

            $users = $this->commerceBranchUserService->getCommerceUsers($commerceId, true, $perPage);
            $resource = CommerceBranchUserResource::collection($users);

            return $this->paginatedResponse($users, $resource, 'Branch leaders retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing branch leaders', [
                'error' => $e->getMessage(),
                'commerce_id' => $request->input('commerce_id'),
            ]);

            return $this->errorResponse('Error retrieving branch leaders', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerce-branch-users",
     *     operationId="storeCommerceBranchUser",
     *     tags={"Commerce Branch Users"},
     *     summary="Create new branch leader and assign to branch",
     *     description="Creates a new user without password, assigns branch_leader role, and sends welcome email with password setup link",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "last_name", "email", "phone", "commerce_branch_id"},
     *
     *             @OA\Property(property="name", type="string", example="Juan"),
     *             @OA\Property(property="last_name", type="string", example="Pérez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com"),
     *             @OA\Property(property="phone", type="string", example="3001234567"),
     *             @OA\Property(property="commerce_branch_id", type="integer", example=1, description="Branch ID to assign user to")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Branch leader created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Branch leader created and assigned successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/CommerceBranchUserResource"),
     *                 @OA\Property(property="message", type="string", example="Welcome email sent with password setup instructions")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only commerce owners can create branch leaders"),
     *     @OA\Response(response=404, description="Branch not found"),
     *     @OA\Response(response=429, description="Too Many Requests")
     * )
     */
    public function store(StoreCommerceBranchUserRequest $request): JsonResponse
    {
        try {
            $userData = $request->only(['name', 'last_name', 'email', 'phone']);
            $commerceBranchId = (int) $request->input('commerce_branch_id');
            $creatorUserId = (int) $request->user()->id;

            $result = $this->commerceBranchUserService->createAndAssign($userData, $commerceBranchId, $creatorUserId);

            return $this->successResponse(
                [
                    'user' => new CommerceBranchUserResource($result['user']),
                    'message' => 'Welcome email sent with password setup instructions',
                ],
                'Commerce Branch leader created and assigned successfully',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            Log::error('Error creating branch leader', [
                'error' => $e->getMessage(),
                'user_email' => $request->input('email'),
                'commerce_branch_id' => $request->input('commerce_branch_id'),
            ]);

            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'does not exist') || str_contains($e->getMessage(), 'No query results for model')) {
                return $this->errorResponse('Commerce branch or related model not found', Response::HTTP_NOT_FOUND);
            }

            if (str_contains($e->getMessage(), 'already exists')) {
                return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
            }

            return $this->errorResponse('Error creating branch leader', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerce-branch-users/assign",
     *     operationId="assignCommerceBranchUser",
     *     tags={"Commerce Branch Users"},
     *     summary="Assign existing user to branch",
     *     description="Assigns an existing user as branch leader to a commerce branch",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"user_id", "commerce_branch_id"},
     *
     *             @OA\Property(property="user_id", type="integer", example=1, description="User ID to assign"),
     *             @OA\Property(property="commerce_branch_id", type="integer", example=1, description="Branch ID to assign user to")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User assigned successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User assigned to branch successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="assignment", type="object",
     *                     @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     *                     @OA\Property(property="branch", ref="#/components/schemas/CommerceBranchResource")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only commerce owners can assign users"),
     *     @OA\Response(response=404, description="User or Branch not found"),
     *     @OA\Response(response=409, description="Conflict - User already assigned to this branch"),
     *     @OA\Response(response=429, description="Too Many Requests")
     * )
     */
    public function assign(AssignCommerceBranchUserRequest $request): JsonResponse
    {
        try {
            $userId = (int) $request->input('user_id');
            $commerceBranchId = (int) $request->input('commerce_branch_id');
            $assignerUserId = (int) $request->user()->id;

            $assignment = $this->commerceBranchUserService->assignUserToBranch($userId, $commerceBranchId, $assignerUserId);

            return $this->successResponse(
                [
                    'assignment' => [
                        'user' => $assignment->user,
                        'commerce_branch' => $assignment->commerceBranch,
                    ],
                ],
                'User assigned to branch successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            Log::error('Error assigning user to branch', [
                'error' => $e->getMessage(),
                'user_id' => $request->input('user_id'),
                'commerce_branch_id' => $request->input('commerce_branch_id'),
            ]);

            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'does not exist') || str_contains($e->getMessage(), 'No query results for model')) {
                return $this->errorResponse('Commerce branch or related model not found', Response::HTTP_NOT_FOUND);
            }

            if (str_contains($e->getMessage(), 'already assigned')) {
                return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
            }

            return $this->errorResponse('Error assigning user to branch', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/commerce-branch-users",
     *     operationId="removeCommerceBranchUser",
     *     tags={"Commerce Branch Users"},
     *     summary="Remove user from branch",
     *     description="Removes a branch leader from a commerce branch",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"user_id", "commerce_branch_id"},
     *
     *             @OA\Property(property="user_id", type="integer", example=1, description="User ID to remove"),
     *             @OA\Property(property="commerce_branch_id", type="integer", example=1, description="Branch ID to remove user from")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User removed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User removed from branch successfully")
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Only commerce owners can remove users"),
     *     @OA\Response(response=404, description="User or Branch not found"),
     *     @OA\Response(response=429, description="Too Many Requests")
     * )
     */
    public function remove(RemoveCommerceBranchUserRequest $request): JsonResponse
    {
        try {
            $userId = (int) $request->input('user_id');
            $commerceBranchId = (int) $request->input('commerce_branch_id');
            $removerUserId = (int) $request->user()->id;

            $this->commerceBranchUserService->removeUserFromBranch($userId, $commerceBranchId, $removerUserId);

            return $this->successResponse(
                null,
                'User removed from branch successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            Log::error('Error removing user from branch', [
                'error' => $e->getMessage(),
                'user_id' => $request->input('user_id'),
                'commerce_branch_id' => $request->input('commerce_branch_id'),
            ]);

            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'does not exist') || str_contains($e->getMessage(), 'No query results for model')) {
                return $this->errorResponse('Commerce branch or related model not found', Response::HTTP_NOT_FOUND);
            }

            return $this->errorResponse('Error removing user from branch', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerce-branch-users/branch/{commerceBranchId}",
     *     operationId="showBranchUsers",
     *     tags={"Commerce Branch Users"},
     *     summary="Get users assigned to a branch",
     *     description="Get paginated list of all users assigned to a specific branch",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="commerceBranchId", in="path", required=true, description="Branch ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", minimum=1, maximum=100, example=15)),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedResponse")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Commerce branch or related model not found"),
     *     @OA\Response(response=429, description="Too Many Requests")
     * )
     */
    public function showBranchUsers(ShowCommerceBranchUsersRequest $request, int $commerceBranchId): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = $request->validatedPerPage();

            $users = $this->commerceBranchUserService->getCommerceBranchUsers($commerceBranchId, true, $perPage);
            $resource = CommerceBranchUserResource::collection($users);

            return $this->paginatedResponse($users, $resource, 'Branch users retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error listing branch users', [
                'error' => $e->getMessage(),
                'commerce_branch_id' => $commerceBranchId,
            ]);

            if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'does not exist') || str_contains($e->getMessage(), 'No query results for model')) {
                return $this->errorResponse('Commerce branch or related model not found', Response::HTTP_NOT_FOUND);
            }

            return $this->errorResponse('Error retrieving branch users', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
