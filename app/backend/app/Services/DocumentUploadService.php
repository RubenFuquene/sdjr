<?php

namespace App\Services;

use App\Constants\Constant;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\CommerceDocument;
use Illuminate\Support\Facades\Storage;

class DocumentUploadService
{
    /**
     * Generar presigned URL para carga
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function generatePresignedUrl(array $data): array
    {
        // Generar token único
        $uploadToken = Str::uuid()->toString();

        // Construir path en S3
        $fileName = $this->sanitizeFileName($data['file_name']);
        
        $path = sprintf(
            'documents/commerce_%d/%s/%s',
            $data['commerce_id'],
            $uploadToken,
            $fileName
        );

        // Generar presigned URL
        $disk = Storage::disk(config('filesystems.default') === 'documents' ? 'documents' : 's3');
      
        $presignedUrl = $disk->temporaryUrl(
            $path,
            now()->addHours(1),
            [
                'ResponseContentType' => $data['mime_type'],
                'ResponseContentDisposition' => 'attachment',
            ]
        );

        return [
            'upload_token' => $uploadToken,
            'presigned_url' => $presignedUrl,
            'expires_in' => 3600,
            'path' => $path,
        ];
    }

    /**
     * Confirmar carga completada
     *
     * @param string $uploadToken
     * @param array $metadata
     * @return array
     * @throws \Exception
     */
    public function confirmUpload(string $uploadToken, array $metadata): array
    {
        // Lógica de confirmación
        // - Validar token existe
        // - Validar no está expirado
        // - Actualizar BD con metadata
        // - Marcar como 'confirmed'
      
        return [
            'success' => true,
            'document_id' => $documentId,
            'status' => 'confirmed',
        ];
    }

    /**
     * Validar entrada de presigned URL
     *
     * @param array $data
     * @throws \Exception
     */
    private function validateInput(array $data): void
    {
        $maxSizeMB = config('filesystems.upload_max_size_mb', 50);
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;

        // Validaciones
        if (empty($data['commerce_id'])) {
            throw new \Exception('commerce_id requerido', 422);
        }

        if (!in_array($data['mime_type'], $this->getAllowedMimeTypes())) {
            throw new \Exception('mime_type no permitido', 422);
        }

        if ($data['file_size_bytes'] > $maxSizeBytes) {
            throw new \Exception("Tamaño máximo: {$maxSizeMB}MB", 422);
        }

        if (strlen($data['file_name']) > 255) {
            throw new \Exception('file_name muy largo', 422);
        }
    }

    /**
     * Sanitizar nombre de archivo
     *
     * @param string $fileName
     * @return string
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Obtener extensión
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
      
        // Generar nombre seguro
        return Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $ext;
    }

    /**
     * MIME types permitidos
     *
     * @return array
     */
    private function getAllowedMimeTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/msword', // .doc
        ];
    }

    /**
     * Almacenar registro de documento
     * @param array $data
     * @param array $result
     * @return CommerceDocument
     */
    public function store(array $data, array $result): CommerceDocument
    {
        return CommerceDocument::create([
            'commerce_id' => $data['commerce_id'],
            'document_type' => $data['document_type'],
            'upload_token' => $result['upload_token'],
            'upload_status' => Constant::UPLOAD_STATUS_PENDING,
            'file_path' => $result['path'],
            'mime_type' => $data['mime_type'],
            'expires_at' => now()->addHour(),
            'uploaded_by_id' => auth()->id(),
            'failed_attempts' => 0,
        ]);
    }   
}