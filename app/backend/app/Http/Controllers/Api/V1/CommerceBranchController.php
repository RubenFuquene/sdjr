<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteCommerceBranchRequest;
use App\Http\Requests\Api\V1\DestroyDocumentUploadRequest;
use App\Http\Requests\Api\V1\IndexCommerceBranchRequest;
use App\Http\Requests\Api\V1\PatchProductPhotoUploadRequest;
use App\Http\Requests\Api\V1\ShowCommerceBranchRequest;
use App\Http\Requests\Api\V1\StoreCommerceBranchRequest;
use App\Http\Requests\Api\V1\UpdateCommerceBranchRequest;
use App\Http\Resources\Api\V1\CommerceBranchResource;
use App\Http\Resources\Api\V1\DocumentUploadResource;
use App\Models\CommerceBranch;
use App\Services\CommerceBranchService;
use App\Services\DocumentUploadService;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Commerce Branches",
 *     description="API Endpoints for Commerce Branches"
 * )
 */
class CommerceBranchController extends Controller
{
    use ApiResponseTrait;

    private CommerceBranchService $commerceService;

    private DocumentUploadService $documentUploadService;

    public function __construct(CommerceBranchService $service)
    {
        $this->commerceService = $service;
        $this->documentUploadService = new DocumentUploadService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerce-branches",
     *     operationId="getCommerceBranches",
     *     tags={"Commerce Branches"},
     *     summary="List all commerce branches",
     *     description="Returns paginated list of all commerce branches.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="name", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="latitude", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="longitude", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="email", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="address", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="phone", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object",
     *
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommerceBranchResource")),
     *         @OA\Property(property="meta", type="object"),
     *         @OA\Property(property="links", type="object")
     *     )),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexCommerceBranchRequest $request): JsonResponse
    {
        $filters = $request->validatedFilters();
        $perPage = $request->validatedPerPage();
        $branches = $this->commerceService->getPaginated($filters, $perPage);
        $resource = CommerceBranchResource::collection($branches);

        return $this->paginatedResponse($branches, $resource, 'Branches retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerce-branches",
     *     operationId="storeCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Create a new branch",
     *     description="Creates a new commerce branch.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreCommerceBranchRequest")),
     *
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/CommerceBranchResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreCommerceBranchRequest $request): JsonResponse
    {
        try {
            $branch = $this->commerceService->store($request->validated());

            return $this->successResponse(new CommerceBranchResource($branch), 'Branch created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating branch', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerce-branches/{id}",
     *     operationId="showCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Show a branch",
     *     description="Returns a single commerce branch.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/CommerceBranchResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(ShowCommerceBranchRequest $request, int $id): JsonResponse
    {
        try {
            $branch = $this->commerceService->show($id);

            return $this->successResponse(new CommerceBranchResource($branch), 'Branch retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Branch not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error retrieving branch', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error retrieving branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/commerce-branches/{id}",
     *     operationId="updateCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Update a branch",
     *     description="Updates a commerce branch.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateCommerceBranchRequest")),
     *
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/CommerceBranchResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(UpdateCommerceBranchRequest $request, int $id): JsonResponse
    {
        try {
            $branch = $this->commerceService->update($id, $request->validated());

            return $this->successResponse(new CommerceBranchResource($branch), 'Branch updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Branch not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error updating branch', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/commerce-branches/{id}",
     *     operationId="deleteCommerceBranch",
     *     tags={"Commerce Branches"},
     *     summary="Delete a branch",
     *     description="Deletes a commerce branch (soft delete).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(DeleteCommerceBranchRequest $request, int $id): JsonResponse
    {
        try {
            $this->commerceService->destroy($id);

            return $this->successResponse(null, 'Branch deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Branch not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error deleting branch', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error deleting branch', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Confirm Commerce Branch photo upload.
     *
     * @OA\Patch(
     *   path="/api/v1/commerce-branches/photos/confirm",
     *   operationId="confirmCommerceBranchPhotoUpload",
     *   tags={"Commerce Branches"},
     *   summary="Confirm commerce branch photo upload",
     *   description="Confirma que la foto de la sucursal del comercio fue subida exitosamente y actualiza el registro.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(ref="#/components/schemas/PatchProductPhotoUploadRequest")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Product photo confirmed successfully",
     *
     *     @OA\JsonContent(ref="#/components/schemas/DocumentUploadResource")
     *   ),
     *
     *   @OA\Response(response=404, description="Photo not exist or not in pending status"),
     *   @OA\Response(response=410, description="Presigned URL expired"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function confirmPhotoUpload(PatchProductPhotoUploadRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            $commerce_branch_photo = $this->documentUploadService->getDocumentPendingByToken(CommerceBranch::class, $data['upload_token']);

            // Validations
            if ($commerce_branch_photo->expires_at < now()) {
                return $this->errorResponse('The presigned URL has expired.', 410);
            }

            // Update photo status to uploaded
            $this->documentUploadService->confirmUpload($commerce_branch_photo, $data);

            return $this->successResponse(new DocumentUploadResource($commerce_branch_photo, [
                'commerce_branch_id' => $commerce_branch_photo->commerce_branch_id]),
                'Commerce branch photo confirmed successfully', 200);

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Photo not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage().' on line '.$e->getLine(), $e->getCode() ?: 500);
        }
    }

    /**
     * Remove a commerce branch photo.
     *
     * @OA\Delete(
     *   path="/api/v1/commerce-branches/photos/{photo}",
     *   operationId="removeCommerceBranchPhoto",
     *   tags={"Commerce Branches"},
     *   summary="Remove commerce branch photo",
     *   description="Elimina una foto de la sucursal del comercio por su ID.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="photo", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=204, description="Photo deleted successfully"),
     *   @OA\Response(response=404, description="Photo not found"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function removePhoto(DestroyDocumentUploadRequest $request, int $photo_id): JsonResponse
    {
        try {

            $this->documentUploadService->removeDocument(CommerceBranch::class, $photo_id);

            return $this->successResponse([], 'Photo deleted successfully', 204);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Photo not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage().' on line '.$e->getLine(), $e->getCode() ?: 500);
        }
    }
}
