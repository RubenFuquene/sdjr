<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EstablishmentTypeRequest;
use App\Http\Resources\Api\V1\EstablishmentTypeResource;
use App\Services\EstablishmentTypeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="EstablishmentTypes",
 *     description="API Endpoints of Establishment Types"
 * )
 */
class EstablishmentTypeController extends Controller
{
    use ApiResponseTrait;

    private EstablishmentTypeService $establishmentTypeService;

    public function __construct(EstablishmentTypeService $establishmentTypeService)
    {
        $this->establishmentTypeService = $establishmentTypeService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/establishment-types",
     *     operationId="getEstablishmentTypesList",
     *     tags={"EstablishmentTypes"},
     *     summary="Get list of establishment types",
     *     description="Returns paginated list of establishment types.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EstablishmentTypeResource")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = request('per_page', 15);
            $types = $this->establishmentTypeService->paginate((int) $perPage);
            $resource = EstablishmentTypeResource::collection($types);

            return $this->paginatedResponse($types, $resource, 'Establishment types retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing establishment types', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error listing establishment types', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/establishment-types",
     *     operationId="storeEstablishmentType",
     *     tags={"EstablishmentTypes"},
     *     summary="Create a new establishment type",
     *     description="Creates a new establishment type.",
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
     *         description="Establishment type created successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EstablishmentTypeResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(EstablishmentTypeRequest $request): JsonResponse
    {
        try {
            $type = $this->establishmentTypeService->store($request->validated());

            return $this->successResponse(new EstablishmentTypeResource($type), 'Establishment type created successfully', Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating establishment type', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating establishment type', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/establishment-types/{id}",
     *     operationId="showEstablishmentType",
     *     tags={"EstablishmentTypes"},
     *     summary="Get an establishment type",
     *     description="Returns a single establishment type.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EstablishmentTypeResource")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show(int $establishmentType_id): JsonResponse
    {
        try {
            return $this->successResponse(new EstablishmentTypeResource($this->establishmentTypeService->show($establishmentType_id)), 'Establishment type retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error showing establishment type', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error showing establishment type', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/establishment-types/{id}",
     *     operationId="updateEstablishmentType",
     *     tags={"EstablishmentTypes"},
     *     summary="Update an establishment type",
     *     description="Updates an establishment type.",
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
     *         description="Establishment type updated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/EstablishmentTypeResource")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function update(EstablishmentTypeRequest $request, int $establishmentType_id): JsonResponse
    {
        try {
            $type = $this->establishmentTypeService->update($establishmentType_id, $request->validated());

            return $this->successResponse(new EstablishmentTypeResource($type), 'Establishment type updated successfully');
        } catch (\Throwable $e) {
            Log::error('Error updating establishment type', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating establishment type', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/establishment-types/{id}",
     *     operationId="deleteEstablishmentType",
     *     tags={"EstablishmentTypes"},
     *     summary="Delete an establishment type",
     *     description="Deletes an establishment type (soft delete).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="Establishment type deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function destroy(int $establishmentType_id): JsonResponse
    {
        try {
            $this->establishmentTypeService->delete($establishmentType_id);

            return $this->successResponse(null, 'Establishment type deleted successfully', Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            Log::error('Error deleting establishment type', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error deleting establishment type', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }
}

