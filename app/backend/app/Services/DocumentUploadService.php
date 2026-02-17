<?php

namespace App\Services;

use App\Constants\Constant;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUploadService
{
    /**
     * Generar presigned URL para carga
     *
     * @throws \Exception
     */
    public function generatePresignedUrl(string $fileName, string $mimeType, int $id, string $source): array
    {
        // Generar token único
        $uploadToken = Str::uuid()->toString();

        // Construir path en S3
        $fileName = $this->sanitizeFileName($fileName);

        $path = sprintf(
            '%s_%d/%s/%s',
            $source,
            $id,
            $uploadToken,
            $fileName
        );

        // Generar presigned URL con cliente S3 apuntando a localhost público
        $presignedUrl = $this->generatePresignedUrlWithPublicUrl($path, $mimeType);

        return [
            'upload_token' => $uploadToken,
            'presigned_url' => $presignedUrl,
            'expires_in' => 3600,
            'path' => '/'.$path,
        ];
    }

    /**
     * Generar presigned URL usando AWS SDK con URL pública
     * Esto asegura que la firma sea válida para localhost:9000
     */
    private function generatePresignedUrlWithPublicUrl(string $key, string $mimeType = ''): string
    {
        $config = config('filesystems.disks.documents');
        $publicUrl = config('filesystems.disks.documents.url'); // http://localhost:9000

        // Crear cliente S3 apuntando a localhost (donde se accede desde navegador)
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'endpoint' => $publicUrl, // ⭐ Usar URL pública para que la firma sea válida
            'use_path_style_endpoint' => $config['use_path_style_endpoint'],
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
        ]);

        // Construir comando PutObject
        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket' => $config['bucket'],
            'Key' => $key,
            'ContentType' => $mimeType ?: 'application/octet-stream',
        ]);

        // Generar presigned request
        $request = $s3Client->createPresignedRequest($cmd, '+1 hour');
        $presignedUrl = (string)$request->getUri();

        return $presignedUrl;
    }

    /**
     * Sanitizar nombre de archivo
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Obtener extensión
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        // Generar nombre seguro
        return Str::slug(pathinfo($fileName, PATHINFO_FILENAME)).'.'.$ext;
    }

    /**
     * Almacenar registro de documento
     */
    public function store($class, array $data, array $result): mixed
    {
        return $class::create([
            'commerce_id' => $data['commerce_id'],
            'document_type' => $data['document_type'] ?? null,
            'upload_token' => $result['upload_token'],
            'upload_status' => Constant::UPLOAD_STATUS_PENDING,
            'file_path' => $result['path'],
            'mime_type' => $data['mime_type'],
            'expires_at' => now()->addHour(),
            'uploaded_by_id' => auth()->id(),
            'failed_attempts' => 0,
        ]);
    }

    /**
     * Obtener documento por token de carga
     */
    public function getDocumentPendingByToken($class, string $uploadToken): mixed
    {
        return $class::where(['upload_token' => $uploadToken, 'upload_status' => Constant::UPLOAD_STATUS_PENDING])->firstOrFail();
    }

    /**
     * Actualizar documento tras confirmación
     */
    public function confirmUpload($model, array $data): bool
    {
        // Convertir last_modified de ISO 8601 a formato MySQL (YYYY-MM-DD HH:MM:SS)
        $lastModified = $data['s3_metadata']['last_modified'];
        if ($lastModified) {
            try {
                $lastModified = \Carbon\Carbon::parse($lastModified)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // Fallback: usar fecha actual si falla el parsing
                $lastModified = now()->format('Y-m-d H:i:s');
            }
        }

        return $model->update([
            'upload_status' => Constant::UPLOAD_STATUS_CONFIRMED,
            's3_etag' => $data['s3_metadata']['etag'],
            's3_object_size' => $data['s3_metadata']['object_size'],
            's3_last_modified' => $lastModified,
            'expires_at' => null,
            'failed_attempts' => 0,
        ]);
    }

    /**
     * Marcar documentos expirados como 'orphaned'.
     *
     * Busca documentos con upload_status = 'pending' y expires_at < NOW(),
     * y actualiza su estado a 'orphaned'.
     *
     * @return int Número de documentos actualizados
     */
    public function markExpiredPendingAsOrphaned($model): int
    {
        return $model::where([
            'upload_status' => Constant::UPLOAD_STATUS_PENDING,
        ])
            ->where('expires_at', '<', now())
            ->update(['upload_status' => Constant::UPLOAD_STATUS_ORPHANED]);
    }

    /**
     * Remove product photo.
     *
     * @throws Exception
     */
    public function removeDocument($class, $document_id): bool
    {
        try {
            $document = $class::findOrFail($document_id);

            return $document->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
