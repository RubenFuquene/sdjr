<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DestroyDocumentUploadRequest;
use App\Http\Requests\Api\V1\PatchDocumentUploadRequest;
use App\Http\Requests\Api\V1\StoreDocumentUploadRequest;
use App\Http\Resources\Api\V1\DocumentUploadResource;
use App\Models\CommerceDocument;
use App\Services\CommerceDocumentService;
use App\Services\DocumentUploadService;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DocumentUploadController extends Controller
{
    use ApiResponseTrait;

    private DocumentUploadService $documentUploadService;

    private CommerceDocumentService $commerceDocumentService;

    public function __construct(DocumentUploadService $documentUploadService, CommerceDocumentService $commerceDocumentService)
    {
        $this->commerceDocumentService = $commerceDocumentService;

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
            // Log the exception for debugging
            Log::error('Document presigned error: '.$e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Only use exception code if it's a valid HTTP status (100-599)
            $exceptionCode = (int) $e->getCode();
            $httpCode = ($exceptionCode >= 100 && $exceptionCode < 600) ? $exceptionCode : 500;

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
            // Log the exception for debugging
            \Log::error('Document confirm error: '.$e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Only use exception code if it's a valid HTTP status (100-599)
            $exceptionCode = (int) $e->getCode();
            $httpCode = ($exceptionCode >= 100 && $exceptionCode < 600) ? $exceptionCode : 500;

            return $this->errorResponse($e->getMessage(), $httpCode);
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
            // Log the exception for debugging
            \Log::error('Document remove error: '.$e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Only use exception code if it's a valid HTTP status (100-599)
            $exceptionCode = (int) $e->getCode();
            $httpCode = ($exceptionCode >= 100 && $exceptionCode < 600) ? $exceptionCode : 500;

            return $this->errorResponse($e->getMessage(), $httpCode);
        }
    }

    /**
     * Generate a presigned download URL for a commerce document.
     *
     * @OA\Get(
     *   path="/api/v1/documents/{id}/download-url",
     *   operationId="downloadCommerceDocumentUrl",
     *   tags={"Documents"},
     *   summary="Generate presigned download URL for a commerce document",
     *   description="Generates a temporary signed URL to securely download a document from S3.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, description="Document ID", @OA\Schema(type="integer")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Download URL generated successfully",
     *
     *     @OA\JsonContent(type="object",
     *
     *       @OA\Property(property="document_id", type="integer", example=123),
     *       @OA\Property(property="url", type="string", example="https://localhost:9000/bucket/..."),
     *       @OA\Property(property="expires_at", type="string", format="date-time", example="2026-02-25T12:00:00Z"),
     *       @OA\Property(property="expired_in_seconds", type="integer", example=900)
     *     )
     *   ),
     *
     *   @OA\Response(response=404, description="Document not found"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function downloadCommerceDocumentUrl(int $id): JsonResponse
    {
        try {
            $commerce_document = $this->commerceDocumentService->find($id);

            $downloadUrl = $this->documentUploadService->generateDownloadUrl($commerce_document->file_path);

            return $this->successResponse([
                'document_id' => $id,
                'url' => $downloadUrl,
                'expires_at' => now()->addMinutes(15)->toISOString(),
                'expired_in_seconds' => 900,
            ], 'Download URL generated successfully');

        } catch (ModelNotFoundException $e) {

            return $this->errorResponse('Document not found', 404);
        } catch (\Exception $e) {

            Log::error('Document download URL error: '.$e->getMessage(), [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
