<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\BankService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BankResource;
use App\Http\Requests\Api\V1\ShowBankRequest;
use App\Http\Requests\Api\V1\IndexBankRequest;
use App\Http\Requests\Api\V1\StoreBankRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Api\V1\DeleteBankRequest;
use App\Http\Requests\Api\V1\UpdateBankRequest;

class BankController extends Controller
{
    use ApiResponseTrait;

    private BankService $bankService;

    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/banks",
     *     operationId="getBanksList",
     *     tags={"Banks"},
     *     summary="Get list of banks",
     *     description="Returns a paginated list of banks.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="name", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="code", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BankResource")), @OA\Property(property="meta", type="object"), @OA\Property(property="links", type="object"))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexBankRequest $request): JsonResponse
    {
        try {
            $filters = $request->only(['name', 'code', 'status']);
            $perPage = $request->validatedPerPage();
            $banks = $this->bankService->getPaginated($filters, $perPage);
            $resource = BankResource::collection($banks);
            return $this->paginatedResponse($banks, $resource, 'Banks retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing banks', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error listing banks', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/banks",
     *     operationId="storeBank",
     *     tags={"Banks"},
     *     summary="Create a new bank",
     *     description="Creates a new bank.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BankStoreRequest")),
     *     @OA\Response(response=201, description="Bank created successfully", @OA\JsonContent(ref="#/components/schemas/BankResource")),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StoreBankRequest $request): JsonResponse
    {
        try {
            $bank = $this->bankService->store($request->validated());
            return $this->successResponse(new BankResource($bank), 'Bank created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating bank', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error creating bank', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/banks/{id}",
     *     operationId="getBankDetail",
     *     tags={"Banks"},
     *     summary="Get bank detail",
     *     description="Returns the detail of a bank.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/BankResource")),
     *     @OA\Response(response=404, description="Bank not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(ShowBankRequest $request, int $id): JsonResponse
    {
        try {
            $bank = $this->bankService->find($id);
            return $this->successResponse(new BankResource($bank), 'Bank retrieved successfully', Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Bank not found', Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Error retrieving bank', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error retrieving bank', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/banks/{id}",
     *     operationId="updateBank",
     *     tags={"Banks"},
     *     summary="Update a bank",
     *     description="Updates the specified bank.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BankUpdateRequest")),
     *     @OA\Response(response=200, description="Bank updated successfully", @OA\JsonContent(ref="#/components/schemas/BankResource")),
     *     @OA\Response(response=404, description="Bank not found"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateBankRequest $request, int $id): JsonResponse
    {
        try {
            $bank = $this->bankService->update($id, $request->validated());
            return $this->successResponse(new BankResource($bank), 'Bank updated successfully', Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Bank not found', Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Error updating bank', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error updating bank', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/banks/{id}",
     *     operationId="deleteBank",
     *     tags={"Banks"},
     *     summary="Delete a bank",
     *     description="Deletes the specified bank (soft delete).",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Bank deleted successfully"),
     *     @OA\Response(response=404, description="Bank not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(DeleteBankRequest $request, int $id): JsonResponse
    {
        try {
            $this->bankService->delete($id);
            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Bank not found', Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Error deleting bank', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error deleting bank', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }
}
