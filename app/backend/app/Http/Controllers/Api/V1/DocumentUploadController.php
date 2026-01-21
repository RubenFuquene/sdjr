<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Services\DocumentUploadService;
use App\Models\CommerceDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentUploadController extends Controller
{
    private DocumentUploadService $uploadService;

    public function __construct(DocumentUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * POST /api/v1/documents/presigned
     * Generar presigned URL para carga
     */
    public function presigned(Request $request): JsonResponse
    {
        $this->authorize('admin.providers.upload_documents');

        try {
            $data = $request->validate([
                'document_type' => 'required|string',
                'file_name' => 'required|string|max:255',
                'mime_type' => 'required|string',
                'file_size_bytes' => 'required|integer|min:1',
                'commerce_id' => 'required|integer|exists:commerces,id',
                'replace_document_id' => 'nullable|integer|exists:commerce_documents,id',
                'versioning_enabled' => 'boolean',
                'metadata' => 'nullable|array',
            ]);

            $result = $this->uploadService->generatePresignedUrl($data);

            // Guardar documento en estado 'pending'
            $document = CommerceDocument::create([
                'commerce_id' => $data['commerce_id'],
                'document_type' => $data['document_type'],
                'upload_token' => $result['upload_token'],
                'upload_status' => 'pending',
                'file_path' => $result['path'],
                'mime_type' => $data['mime_type'],
                'expires_at' => now()->addHour(),
                'uploaded_by_id' => auth()->id(),
                'failed_attempts' => 0,
            ]);

            return response()->json([
                'upload_token' => $result['upload_token'],
                'presigned_url' => $result['presigned_url'],
                'expires_in' => $result['expires_in'],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/v1/documents/confirm
     * Confirmar carga completada
     */
    public function confirm(Request $request): JsonResponse
    {
        $this->authorize('admin.providers.upload_documents');

        try {
            $data = $request->validate([
                'upload_token' => 'required|string|exists:commerce_documents,upload_token',
                's3_metadata' => 'required|array',
                's3_metadata.etag' => 'required|string',
                's3_metadata.object_size' => 'required|integer|min:1',
                's3_metadata.last_modified' => 'required|string',
            ]);

            $document = CommerceDocument::where('upload_token', $data['upload_token'])->first();

            // Validaciones
            if ($document->upload_status !== 'pending') {
                return response()->json(['error' => 'Documento no está en estado pending'], 400);
            }

            if ($document->expires_at < now()) {
                return response()->json(['error' => 'Presigned URL expirada'], 410);
            }

            // Actualizar documento
            $document->update([
                'upload_status' => 'confirmed',
                's3_etag' => $data['s3_metadata']['etag'],
                's3_object_size' => $data['s3_metadata']['object_size'],
                's3_last_modified' => $data['s3_metadata']['last_modified'],
                'expires_at' => null,
                'failed_attempts' => 0,
            ]);

            return response()->json($document->toArray(), 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}