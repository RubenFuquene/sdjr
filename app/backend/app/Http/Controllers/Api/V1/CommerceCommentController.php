<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteCommerceCommentRequest;
use App\Http\Requests\Api\V1\IndexCommerceCommentRequest;
use App\Http\Requests\Api\V1\ShowCommerceCommentRequest;
use App\Http\Requests\Api\V1\StoreCommerceCommentRequest;
use App\Http\Requests\Api\V1\UpdateCommerceCommentRequest;
use App\Http\Resources\Api\V1\CommerceCommentResource;
use App\Services\CommerceCommentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Commerce Comments",
 *     description="API Endpoints for Commerce Comments"
 * )
 */
class CommerceCommentController extends Controller
{
    use ApiResponseTrait;

    protected $commerceCommentService;

    public function __construct(CommerceCommentService $service)
    {
        $this->commerceCommentService = $service;
    }

    /**
     * List commerce comments
     *
     * @OA\Get(
     *     path="/api/v1/commerces/{commerce_id}/comments",
     *     summary="List comments for a commerce",
     *     tags={"CommerceComments"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="commerce_id",
     *         in="path",
     *         required=true,
     *         description="ID of the commerce",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceCommentResourceCollection")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexCommerceCommentRequest $request, int $commerce_id): JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $comments = $this->commerceCommentService->getCommentsByCommerce($commerce_id, $filters, $perPage);
            $resource = CommerceCommentResource::collection($comments);

            return $this->paginatedResponse($comments, $resource, 'Comments retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Failed to fetch comments', ['error' => $e->getMessage(), 'commerce_id' => $commerce_id, 'filters' => $request->all()]);

            return $this->errorResponse('Failed to fetch comments', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerces/{commerce_id}/comments",
     *     summary="Create a new comment for a commerce",
     *     tags={"CommerceComments"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreCommerceCommentRequest")
     *     ),
     *
     *     @OA\Parameter(
     *         name="commerce_id",
     *         in="path",
     *         required=true,
     *         description="ID of the commerce",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Comment created",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceCommentResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreCommerceCommentRequest $request, int $commerce_id): JsonResponse
    {
        try {
            $comment = $this->commerceCommentService->createComment($commerce_id, $request->validated());

            return $this->successResponse(new CommerceCommentResource($comment), 'Comment created successfully', 201);
        } catch (\Exception $e) {
            Log::error('Failed to create comment', ['error' => $e->getMessage(), 'commerce_id' => $commerce_id, 'data' => $request->all()]);

            return $this->errorResponse('Failed to create comment', 500);
        }
    }

    /**
     * Show a specific comment
     *
     * @OA\Get(
     *     path="/api/v1/commerces/{commerce_id}/comments/{id}",
     *     summary="Show a specific comment",
     *     tags={"CommerceComments"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="commerce_id",
     *         in="path",
     *         required=true,
     *         description="ID of the commerce",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceCommentResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(ShowCommerceCommentRequest $request, int $commerce_id, int $id): JsonResponse
    {
        try {
            $comment = $this->commerceCommentService->getComment($commerce_id, $id);

            return $this->successResponse(new CommerceCommentResource($comment), 'Comment retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Comment not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch comment', 500);
        }
    }

    /**
     * Update a comment
     *
     * @OA\Put(
     *     path="/api/v1/commerces/{commerce_id}/comments/{id}",
     *     summary="Update a comment",
     *     tags={"CommerceComments"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateCommerceCommentRequest")
     *     ),
     *
     *     @OA\Parameter(
     *         name="commerce_id",
     *         in="path",
     *         required=true,
     *         description="ID of the commerce",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceCommentResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateCommerceCommentRequest $request, int $commerce_id, int $id): JsonResponse
    {
        try {
            $comment = $this->commerceCommentService->updateComment($commerce_id, $id, $request->validated());

            return $this->successResponse(new CommerceCommentResource($comment), 'Comment updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Comment not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update comment', 500);
        }
    }

    /**
     * Delete a comment
     *
     * @OA\Delete(
     *     path="/api/v1/commerces/{commerce_id}/comments/{id}",
     *     summary="Delete a comment",
     *     tags={"CommerceComments"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="commerce_id",
     *         in="path",
     *         required=true,
     *         description="ID of the commerce",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Comment deleted"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(DeleteCommerceCommentRequest $request, int $commerce_id, int $id): JsonResponse
    {
        try {
            $this->commerceCommentService->deleteComment($commerce_id, $id);

            return $this->successResponse(null, 'Comment deleted successfully', 204);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Comment not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete comment', 500);
        }
    }
}
