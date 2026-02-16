<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DestroyDocumentUploadRequest;
use App\Http\Requests\Api\V1\PatchDocumentUploadRequest;
use App\Http\Requests\Api\V1\StoreDocumentUploadRequest;
use App\Http\Resources\Api\V1\DocumentUploadResource;
use App\Models\CommerceDocument;
use App\Services\DocumentUploadService;
use App\Traits\ApiResponseTrait;
use Dom\Document;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class DocumentUploadController extends Controller
{
    use ApiResponseTrait;

    private DocumentUploadService $documentUploadService;

    public function __construct(DocumentUploadService $documentUploadService)
    {
        $this->documentUploadService = $documentUploadService;
    }

    /**
     * Generate presigned URL for document upload.
     *
     * @OA\Post(
     *   path="/api/v1/documents/presigned",
     *   operationId="generatePresignedDocumentUrl",
     *   tags={"Documents"},
     *   summary="Generate presigned URL for document upload",
     *   description="Generates a signed URL to upload a document to S3 and creates a temporary record.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(ref="#/components/schemas/StoreDocumentUploadRequest")
     *   ),
     *
     *   @OA\Response(
     *     response=201,
     *     description="Presigned URL generated successfully",
     *
     *     @OA\JsonContent(type="object",
     *
     *       @OA\Property(property="upload_token", type="string", example="token123"),
     *       @OA\Property(property="presigned_url", type="string", example="https://s3.amazonaws.com/..."),
     *       @OA\Property(property="expires_in", type="integer", example=3600)
     *     )
     *   ),
     *
     *   @OA\Response(response=400, description="Bad Request"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function presigned(StoreDocumentUploadRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            $result = $this->documentUploadService->generatePresignedUrl($data['file_name'], $data['mime_type'], $data['commerce_id'], 'commerce');

            // Guardar registro temporal en la base de datos
            $this->documentUploadService->store(CommerceDocument::class, $data, $result);

            return $this->successResponse([
                'upload_token' => $result['upload_token'],
                'presigned_url' => $result['presigned_url'],
                'expires_in' => $result['expires_in'],
                'path' => $result['path'],
            ], 'Presigned URL generate successful.', 201);

        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            $httpCode = $code >= 100 ? $code : 500;

            return $this->errorResponse($e->getMessage(), $httpCode);
        }
    }

    /**
     * Confirm document upload.
     *
     * @OA\Post(
     *   path="/api/v1/documents/confirm",
     *   operationId="confirmDocumentUpload",
     *   tags={"Documents"},
     *   summary="Confirm document upload",
     *   description="Confirm that the document was uploaded successfully and update the record.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(ref="#/components/schemas/PatchDocumentUploadRequest")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Document confirmed successfully",
     *
     *     @OA\JsonContent(ref="#/components/schemas/DocumentUploadResource")
     *   ),
     *
     *   @OA\Response(response=404, description="Document not exist or not in pending status"),
     *   @OA\Response(response=410, description="Presigned URL expired"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function confirm(PatchDocumentUploadRequest $request): JsonResponse
    {

        try {
            $data = $request->validated();

            $document = $this->documentUploadService->getDocumentPendingByToken(CommerceDocument::class, $data['upload_token']);

            // Validations
            if ($document->expires_at < now()) {
                return $this->errorResponse('The presigned URL has expired.', 410);
            }

            // Update document status to uploaded
            $this->documentUploadService->confirmUpload($document, $data);

            return $this->successResponse(new DocumentUploadResource($document, [
                'commerce_id' => $document->commerce_id,
                'document_type' => $document->document_type,
            ]), 'Document confirmed successfully', 200);

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Document not found', 404);

        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            $httpCode = $code >= 100 ? $code : 500;

            return $this->errorResponse($e->getMessage().' on line '.$e->getLine(), $httpCode);
        }
    }

    /**
     * Remove a document.
     *
     * @OA\Delete(
     *   path="/api/v1/documents/{document}",
     *   operationId="removeDocument",
     *   tags={"Documents"},
     *   summary="Remove document",
     *   description="Elimina un documento por su ID.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="document", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=204, description="Document deleted successfully"),
     *   @OA\Response(response=404, description="Document not found"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function remove(DestroyDocumentUploadRequest $request, int $document_id): JsonResponse
    {
        try {

            $this->documentUploadService->removeDocument(CommerceDocument::class, $document_id);

            return $this->successResponse([], 'Document deleted successfully', 204);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Document not found', 404);
        } catch (\Exception $e) {
            $code = (int) $e->getCode();
            $httpCode = $code >= 100 ? $code : 500;

            return $this->errorResponse($e->getMessage().' on line '.$e->getLine(), $httpCode);
        }
    }
}
