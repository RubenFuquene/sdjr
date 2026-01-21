<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexLegalDocumentRequest;
use App\Http\Resources\Api\V1\LegalDocumentResource;
use App\Services\LegalDocumentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(
 *     name="LegalDocuments",
 *     description="API Endpoints of Legal Documents"
 * )
 */
class LegalDocumentController extends Controller
{
    use ApiResponseTrait;

    private LegalDocumentService $legalDocumentService;

    public function __construct(LegalDocumentService $legalDocumentService)
    {
        $this->legalDocumentService = $legalDocumentService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/legal-documents",
     *     operationId="getLegalDocumentsList",
     *     tags={"LegalDocuments"},
     *     summary="Get list of legal documents",
     *     description="Returns paginated list of legal documents. Permite filtrar por tipo, estado, cantidad por página y número de página.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object",
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LegalDocumentResource")),
     *         @OA\Property(property="meta", type="object"),
     *         @OA\Property(property="links", type="object")
     *     )),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexLegalDocumentRequest $request): JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $documents = $this->legalDocumentService->getPaginated($filters, $perPage);
            $resource = LegalDocumentResource::collection($documents);
            return $this->paginatedResponse($documents, $resource, 'Legal documents retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error listing legal documents', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error listing legal documents', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/legal-documents/{type}",
     *     operationId="getLegalDocumentByType",
     *     tags={"LegalDocuments"},
     *     summary="Get latest active legal document by type",
     *     description="Returns the latest active legal document for a given type.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="type", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/LegalDocumentResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function showByType(string $type, IndexLegalDocumentRequest $request): JsonResponse
    {
        try {
            $document = $this->legalDocumentService->getLatestByType($type);
            if (!$document) {
                return $this->errorResponse('Legal document not found', Response::HTTP_NOT_FOUND);
            }
            return $this->successResponse(new LegalDocumentResource($document), 'Legal document retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Error retrieving legal document', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error retrieving legal document', Response::HTTP_INTERNAL_SERVER_ERROR, ['exception' => $e->getMessage()]);
        }
    }
}
