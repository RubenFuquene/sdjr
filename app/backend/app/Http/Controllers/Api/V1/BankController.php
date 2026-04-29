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
     *     description="Retrieve a paginated list of banks. Filter by name, code, status, or items per page.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false, description="Bank name (partial match)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="code", in="query", required=false, description="Bank code (ISO)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Bank status: 1=active, 0=inactive", @OA\Schema(type="string", enum={"1","0"}, default="1")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="sort_by", in="query", required=false, description="Sort field", @OA\Schema(type="string", enum={"name","code","status","created_at","updated_at"}, default="name")),
     *     @OA\Parameter(name="sort_dir", in="query", required=false, description="Sort direction", @OA\Schema(type="string", enum={"asc","desc"}, default="asc")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", example={"data":{{"id":1,"name":"Bank of America","code":"BOA","status":"1"},{"id":2,"name":"Citibank","code":"CITI","status":"1"}},"meta":{"current_page":1,"total":2,"per_page":15},"links":{"first":"/?page=1","last":"/?page=1","prev":null,"next":null}})),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(
     *         response=429,
     *         description="Too Many Requests",
     *
     *         @OA\JsonContent(example={"status":false,"message":"Too many requests. Please try again later.","code":429}),
     *
     *         @OA\Header(header="Retry-After", description="Segundos hasta que se puede volver a intentar", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-RateLimit-Limit", description="Límite de peticiones por ventana", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-RateLimit-Remaining", description="Peticiones restantes en la ventana actual", @OA\Schema(type="integer")),
     *         @OA\Header(header="X-RateLimit-Reset", description="Timestamp de reseteo de ventana", @OA\Schema(type="integer"))
     *     )
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
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreBankRequest")),
     *
     *     @OA\Response(response=201, description="Bank created successfully", @OA\JsonContent(ref="#/components/schemas/BankResource")),
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
     *     @OA\Parameter(name="id", in="path", required=true, description="Bank ID", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/BankResource")),
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
     *     @OA\Parameter(name="id", in="path", required=true, description="Bank ID", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(@OA\Property(property="name", type="string", example="Banco de la Nación"), @OA\Property(property="code", type="string", example="BN"), @OA\Property(property="status", type="string", example="active"))),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/BankResource")),
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
     *     @OA\Parameter(name="id", in="path", required=true, description="Bank ID", @OA\Schema(type="integer")),
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
