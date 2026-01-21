<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiResponseTrait;
use App\Services\CommerceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\CommerceBranchService;
use App\Http\Requests\CommerceBranchRequest;
use App\Http\Requests\Api\V1\CommerceRequest;
use App\Http\Resources\CommerceBranchResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Api\V1\CommerceResource;
use App\Http\Requests\Api\V1\IndexCommerceRequest;
use App\Http\Requests\Api\V1\IndexCommerceBranchRequest;
use App\Http\Requests\Api\V1\PatchCommerceStatusRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Api\V1\PatchCommerceVerificationRequest;
use App\Http\Requests\Api\V1\IndexCommercePayoutMethodRequest;
use App\Services\CommercePayoutMethodService;
use App\Http\Resources\Api\V1\CommercePayoutMethodResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Commerces",
 *     description="API Endpoints of Commerces"
 * )
 */
class CommerceController extends Controller
{
    use ApiResponseTrait;

    private CommerceService $commerceService;

    private CommerceBranchService $commerceBranchService;

    private CommercePayoutMethodService $commercePayoutMethodService;

    public function __construct(
        CommerceService $commerceService,
        CommerceBranchService $commerceBranchService,
        CommercePayoutMethodService $commercePayoutMethodService
    ) {
        $this->commerceService = $commerceService;
        $this->commerceBranchService = $commerceBranchService;
        $this->commercePayoutMethodService = $commercePayoutMethodService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerces",
     *     operationId="getCommercesList",
     *     tags={"Commerces"},
     *     summary="Get list of commerces",
     *     description="Returns paginated list of commerces. Permite filtrar por término de búsqueda (search), estado (status), cantidad por página (per_page) y número de página (page).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="search", in="query", required=false, description="Filtrar por término de búsqueda", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filtrar por estado: 1=activos, 0=inactivos", @OA\Schema(type="string", enum={"1","0"}, default="1")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, description="Número de página", @OA\Schema(type="integer", default=1)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object",
     *
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommerceResource")),
     *         @OA\Property(property="meta", type="object"),
     *         @OA\Property(property="links", type="object")
     *     )),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(IndexCommerceRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $page = $request->validatedPage();
            $commerces = $this->commerceService->paginateWithFilters($perPage, $page, $filters['search'] ?? null, $filters['status'] ?? null);
            $resource = CommerceResource::collection($commerces);

            return $this->paginatedResponse($commerces, $resource, 'Commerces retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing commerces', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error listing commerces', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerces",
     *     operationId="storeCommerce",
     *     tags={"Commerces"},
     *     summary="Create a new commerce",
     *     description="Creates a new commerce.",
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
     *         description="Commerce created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(CommerceRequest $request): JsonResponse
    {
        try {
            $commerce = $this->commerceService->store($request->validated());

            return $this->successResponse(new CommerceResource($commerce), 'Commerce created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating commerce', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerces/{id}",
     *     operationId="showCommerce",
     *     tags={"Commerces"},
     *     summary="Get a commerce",
     *     description="Returns a single commerce.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(int $commerce_id): JsonResponse
    {
        try {
            return $this->successResponse(new CommerceResource($this->commerceService->show($commerce_id)), 'Commerce retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error showing commerce', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error showing commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/commerces/{id}",
     *     operationId="updateCommerce",
     *     tags={"Commerces"},
     *     summary="Update a commerce",
     *     description="Updates a commerce.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(type="object")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Commerce updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function update(CommerceRequest $request, int $commerce_id): JsonResponse
    {
        try {
            $commerce = $this->commerceService->update($commerce_id, $request->validated());

            return $this->successResponse(new CommerceResource($commerce), 'Commerce updated successfully');
        } catch (\Throwable $e) {
            Log::error('Error updating commerce', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/commerces/{id}",
     *     operationId="deleteCommerce",
     *     tags={"Commerces"},
     *     summary="Delete a commerce",
     *     description="Deletes a commerce (soft delete).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="Commerce deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(int $commerce_id): JsonResponse
    {
        try {
            $this->commerceService->delete($commerce_id);

            return $this->successResponse(null, 'Commerce deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Commerce not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error deleting commerce', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error deleting commerce', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/commerces/{id}/status",
     *     operationId="patchCommerceStatus",
     *     tags={"Commerces"},
     *     summary="Update commerce status",
     *     description="Updates the is_active status of a commerce (active/inactive).",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Commerce ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_active"},     
     *             @OA\Property(property="is_active", type="integer", enum={1,0}, example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commerce status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *     @OA\Response(response=404, description="Commerce not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function patchStatus(PatchCommerceStatusRequest $request, int $id): JsonResponse
    {
        try {
            $commerce = $this->commerceService->updateStatus(
                $id,
                (int) $request->validated('is_active')
            );
            return $this->successResponse(new CommerceResource($commerce), 'Commerce status updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Commerce not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error updating commerce status', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error updating commerce status', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/commerces/{id}/verification",
     *     operationId="patchCommerceVerification",
     *     tags={"Commerces"},
     *     summary="Update commerce verification status",
     *     description="Updates the is_verified status of a commerce (verified/unverified).",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Commerce ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_verified"},
     *             @OA\Property(property="is_verified", type="integer", enum={1,0}, example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commerce verification updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommerceResource")
     *     ),
     *     @OA\Response(response=404, description="Commerce not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function patchVerification(PatchCommerceVerificationRequest $request, int $id): JsonResponse
    {
        try {
            $commerce = $this->commerceService->updateVerification($id,  (int) $request->validated('is_verified'));
            return $this->successResponse(new CommerceResource($commerce), 'Commerce verification updated successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Commerce not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error updating commerce verification', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error updating commerce verification', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerces/{commerce_id}/branches",
     *     operationId="getBranchesByCommerceId",
     *     tags={"Commerces"},
     *     summary="List branches by commerce",
     *     description="Returns paginated list of branches for a specific commerce.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="commerce_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object",
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommerceBranchResource")),
     *         @OA\Property(property="meta", type="object"),
     *         @OA\Property(property="links", type="object")
     *     )),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Commerce not found")
     * )
     */
    public function getBranchesByCommerceId(int $commerce_id, IndexCommerceBranchRequest $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 15);
            $branches = $this->commerceBranchService->getBranchesByCommerceId($commerce_id, (int)$perPage);
            $resource = CommerceBranchResource::collection($branches);
            return $this->paginatedResponse($branches, $resource, 'Branches retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Commerce not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error listing branches', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error listing branches', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerces/{commerce_id}/payout-methods",
     *     operationId="getPayoutMethodsByCommerceId",
     *     tags={"Commerces"},
     *     summary="List payout methods by commerce",
     *     description="Returns paginated list of payout methods for a specific commerce.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="commerce_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="owner_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="account_number", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", maxLength=1)),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object",
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommercePayoutMethodResource")),
     *         @OA\Property(property="meta", type="object"),
     *         @OA\Property(property="links", type="object")
     *     )),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Commerce not found")
     * )
     */
    public function getPayoutMethodsByCommerceId(int $commerce_id, IndexCommercePayoutMethodRequest $request): JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $filters['commerce_id'] = $commerce_id;
            $payoutMethods = $this->commercePayoutMethodService->getPaginated($filters, $perPage);
            $resource = CommercePayoutMethodResource::collection($payoutMethods);
            return $this->paginatedResponse($payoutMethods, $resource, 'Payout methods retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Commerce not found', 404);
        } catch (\Throwable $e) {
            Log::error('Error listing payout methods', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error listing payout methods', 500);
        }
    }

}
