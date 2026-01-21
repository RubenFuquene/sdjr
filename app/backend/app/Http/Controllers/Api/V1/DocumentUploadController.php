<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Services\DocumentUploadService;
use App\Models\CommerceDocument;
use App\Http\Requests\Api\V1\StoreDocumentUploadRequest;
use App\Http\Requests\Api\V1\PatchDocumentUploadRequest;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;

class DocumentUploadController extends Controller
{
    use ApiResponseTrait;

    private DocumentUploadService $uploadService;

    public function __construct(DocumentUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Generar presigned URL para carga de documentos.
     *
     * @OA\Post(
     *   path="/api/v1/documents/presigned",
     *   operationId="generatePresignedDocumentUrl",
     *   tags={"Documents"},
     *   summary="Generar presigned URL para carga de documentos",
     *   description="Genera una URL firmada para cargar un documento a S3 y crea el registro temporal.",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StoreDocumentUploadRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Presigned URL generada",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="upload_token", type="string", example="token123"),
     *       @OA\Property(property="presigned_url", type="string", example="https://s3.amazonaws.com/..."),
     *       @OA\Property(property="expires_in", type="integer", example=3600)
     *     )
     *   ),
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

            $result = $this->uploadService->generatePresignedUrl($data);

            // Guardar registro temporal en la base de datos
            $this->uploadService->store($data, $result);

            return $this->successResponse([
                'upload_token' => $result['upload_token'],
                'presigned_url' => $result['presigned_url'],
                'expires_in' => $result['expires_in'],
            ], 'Presigned URL generate successful.', 201);            

        } catch (\Exception $e) {

            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Confirmar carga completada de documento.
     *
     * @OA\Post(
     *   path="/api/v1/documents/confirm",
     *   operationId="confirmDocumentUpload",
     *   tags={"Documents"},
     *   summary="Confirmar carga de documento",
     *   description="Confirma que el archivo fue cargado exitosamente a S3 y actualiza el registro.",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/PatchDocumentUploadRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Documento confirmado",
     *     @OA\JsonContent(type="object")
     *   ),
     *   @OA\Response(response=400, description="Documento no está en estado pending"),
     *   @OA\Response(response=410, description="Presigned URL expirada"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function confirm(PatchDocumentUploadRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
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