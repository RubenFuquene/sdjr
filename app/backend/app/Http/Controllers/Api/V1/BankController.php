<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteBankRequest;
use App\Http\Requests\Api\V1\IndexBankRequest;
use App\Http\Requests\Api\V1\ShowBankRequest;
use App\Http\Requests\Api\V1\StoreBankRequest;
use App\Http\Requests\Api\V1\UpdateBankRequest;
use App\Http\Resources\Api\V1\BankResource;
use App\Services\BankService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="Banks",
 *     description="API Endpoints of Banks"
 * )
 */
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
     *     operationId="indexBanks",
     *     tags={"Banks"},
     *     summary="List banks",
     *     description="Get paginated list of banks. Permite filtrar por nombre (name), código (code), estado (status) y cantidad por página (per_page).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false, description="Filtrar por nombre del banco (texto parcial)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="code", in="query", required=false, description="Filtrar por código del banco (ISO)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filtrar por estado: 1=activos, 0=inactivos", @OA\Schema(type="string", enum={"1","0"}, default="1")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", example=15)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexBankRequest $request): JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
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
     * Store a newly created bank.
     *
     * @OA\Post(
     *     path="/api/v1/banks",
     *     operationId="storeBank",
     *     tags={"Banks"},
     *     summary="Create new bank",
     *     description="Creates a new bank record.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreBankRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Bank created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/BankResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
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
     * Display the specified bank.
     *
     * @OA\Get(
     *     path="/api/v1/banks/{id}",
     *     operationId="showBank",
     *     tags={"Banks"},
     *     summary="Get bank information",
     *     description="Returns bank data",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bank ID",
     *
     *         @OA\Schema(ref="#/components/schemas/ShowBankRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/BankResource")
     *     ),
     *
     *     @OA\Response(response=404, description="Resource Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
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
     * Update the specified bank.
     *
     * @OA\Put(
     *     path="/api/v1/banks/{id}",
     *     operationId="updateBank",
     *     tags={"Banks"},
     *     summary="Update existing bank",
     *     description="Returns updated bank data",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bank ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateBankRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/BankResource")
     *     ),
     *
     *     @OA\Response(response=404, description="Resource Not Found"),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
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
     * Remove the specified bank.
     *
     * @OA\Delete(
     *     path="/api/v1/banks/{id}",
     *     operationId="deleteBank",
     *     tags={"Banks"},
     *     summary="Delete existing bank",
     *     description="Deletes a record and returns no content",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bank ID",
     *
     *         @OA\Schema(ref="#/components/schemas/DeleteBankRequest")
     *     ),
     *
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
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
