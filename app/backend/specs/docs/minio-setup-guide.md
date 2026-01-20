# Configuración de MinIO para Desarrollo Local

**Fecha:** 2026-01-19
**Propósito:** Setup completo de MinIO en Docker para flujo de carga de documentos

---

## 1. Docker Compose Actualizado

Agregar a `infra/docker-compose.yml`:

```yaml
version: '3.8'

services:
  # ... servicios existentes (postgres, redis, etc.) ...

  # MinIO Object Storage
  minio:
    image: minio/minio:latest
    container_name: sdjr_minio
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin123
      MINIO_BROWSER_REDIRECT_URL: http://minio:9001
    ports:
      - "9000:9000"      # API endpoint
      - "9001:9001"      # Web console
    volumes:
      - minio_data:/data
      - ./minio_init.sh:/tmp/minio_init.sh
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:9000/minio/health/live"]
      interval: 30s
      timeout: 20s
      retries: 3
    command: minio server /data --console-address ":9001"
    networks:
      - sdjr_network

volumes:
  minio_data:
    driver: local

networks:
  sdjr_network:
    driver: bridge
```

---

## 2. Script de Inicialización MinIO

Crear `infra/minio_init.sh`:

```bash
#!/bin/bash

# Esperar a que MinIO esté listo
echo "Esperando a MinIO..."
sleep 10

# Variables
MINIO_ENDPOINT="http://minio:9000"
MINIO_ACCESS_KEY="minioadmin"
MINIO_SECRET_KEY="minioadmin123"
BUCKET_NAME="documents"

# Verificar si MinIO está disponible
while ! curl -f "${MINIO_ENDPOINT}/minio/health/live" > /dev/null 2>&1; do
    echo "MinIO no está listo..."
    sleep 2
done

echo "MinIO está listo ✅"

# Crear bucket si no existe
echo "Creando bucket '${BUCKET_NAME}'..."
mc alias set minio ${MINIO_ENDPOINT} ${MINIO_ACCESS_KEY} ${MINIO_SECRET_KEY} || true
mc mb minio/${BUCKET_NAME} --ignore-existing || true

# Configurar política de bucket para presigned URLs
echo "Configurando políticas de bucket..."
cat > /tmp/policy.json << EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": "*",
      "Action": [
        "s3:GetObject",
        "s3:PutObject"
      ],
      "Resource": "arn:aws:s3:::${BUCKET_NAME}/*"
    }
  ]
}
EOF

mc policy set /tmp/policy.json minio/${BUCKET_NAME} || true

echo "Bucket '${BUCKET_NAME}' listo ✅"

# Listar buckets
echo "Buckets disponibles:"
mc ls minio/

exit 0
```

Hacer ejecutable:

```bash
chmod +x infra/minio_init.sh
```

---

## 3. Configuración Laravel

### `config/filesystems.php`

```php
<?php

return [
    // ... disks existentes ...

    'disks' => [
        // ... otros disks ...

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET', 'documents'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT', 'https://s3.amazonaws.com'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'private',  // ⭐ Importante: privado por defecto
        ],

        // Alias específico para documentos (usa configuración s3)
        'documents' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET', 'documents'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'visibility' => 'private',
        ],
    ],

    // ... resto de configuración ...
];
```

### `.env` (Local)

```env
# AWS / MinIO Configuration
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin123
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=documents
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_URL=http://localhost:9000

# File Upload Configuration
FILE_UPLOAD_MAX_SIZE_MB=50
STORAGE_QUOTA_PER_COMMERCE_GB=5
DOCUMENT_UPLOAD_TOKEN_EXPIRATION_MINUTES=60
ORPHANED_CLEANUP_AFTER_HOURS=24
DELETE_OLD_FILES_AFTER_REPLACEMENT=false
```

### `.env.staging` (Staging)

```env
# AWS S3 Staging
AWS_ACCESS_KEY_ID=your-staging-key
AWS_SECRET_ACCESS_KEY=your-staging-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=documents-staging
AWS_ENDPOINT=https://s3.staging.railway.app
AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_URL=https://s3.staging.railway.app/documents-staging

# File Upload Configuration
FILE_UPLOAD_MAX_SIZE_MB=50
STORAGE_QUOTA_PER_COMMERCE_GB=5
DOCUMENT_UPLOAD_TOKEN_EXPIRATION_MINUTES=60
ORPHANED_CLEANUP_AFTER_HOURS=24
DELETE_OLD_FILES_AFTER_REPLACEMENT=false
```

---

## 4. Servicio de Carga de Documentos

Crear `app/backend/app/Services/DocumentUploadService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        // Validaciones
        $this->validateInput($data);

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
}
```

---

## 5. Controlador de Documentos

Crear `app/backend/app/Http/Controllers/DocumentUploadController.php`:

```php
<?php

namespace App\Http\Controllers;

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
```

---

## 6. Rutas API

Agregar a `app/backend/routes/api.php`:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    // ... otras rutas ...

    // Document Upload Endpoints
    Route::prefix('documents')->group(function () {
        Route::post('/presigned', [DocumentUploadController::class, 'presigned']);
        Route::post('/confirm', [DocumentUploadController::class, 'confirm']);
    });
});
```

---

## 7. Migración de BD

Crear `database/migrations/2026_01_19_000001_update_commerce_documents_for_upload.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_documents', function (Blueprint $table) {
            $table->string('upload_token', 255)->unique()->nullable()->after('file_path');
            $table->enum('upload_status', ['pending', 'confirmed', 'failed', 'orphaned'])->default('pending')->after('upload_token');
            $table->string('s3_etag', 255)->nullable()->after('upload_status');
            $table->bigInteger('s3_object_size')->nullable()->after('s3_etag');
            $table->dateTime('s3_last_modified')->nullable()->after('s3_object_size');
            $table->unsignedBigInteger('replacement_of_id')->nullable()->after('s3_last_modified');
            $table->unsignedBigInteger('version_of_id')->nullable()->after('replacement_of_id');
            $table->unsignedInteger('version_number')->default(1)->after('version_of_id');
            $table->dateTime('expires_at')->nullable()->after('version_number');
            $table->unsignedInteger('failed_attempts')->default(0)->after('expires_at');
          
            // Índices
            $table->index('upload_token');
            $table->index('upload_status');
            $table->index('expires_at');
            $table->index(['commerce_id', 'upload_status']);
          
            // Foreign Keys
            $table->foreign('replacement_of_id')->references('id')->on('commerce_documents')->onDelete('setNull');
            $table->foreign('version_of_id')->references('id')->on('commerce_documents')->onDelete('setNull');
        });
    }

    public function down(): void
    {
        Schema::table('commerce_documents', function (Blueprint $table) {
            $table->dropForeign(['replacement_of_id']);
            $table->dropForeign(['version_of_id']);
            $table->dropIndex(['upload_token']);
            $table->dropIndex(['upload_status']);
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['commerce_id', 'upload_status']);
          
            $table->dropColumn([
                'upload_token',
                'upload_status',
                's3_etag',
                's3_object_size',
                's3_last_modified',
                'replacement_of_id',
                'version_of_id',
                'version_number',
                'expires_at',
                'failed_attempts',
            ]);
        });
    }
};
```

---

## 8. Comandos de Setup

```bash
# 1. Actualizar docker-compose
# Copiar contenido de sección 1 a infra/docker-compose.yml

# 2. Iniciar MinIO
docker-compose -f infra/docker-compose.yml up -d minio

# 3. Ejecutar script de inicialización
docker exec sdjr_minio sh /tmp/minio_init.sh

# 4. Ejecutar migración
cd app/backend
php artisan migrate

# 5. Verificar acceso a MinIO Console
# Abrir en navegador: http://localhost:9001
# Usuario: minioadmin
# Contraseña: minioadmin123
```

---

## 9. Testing con cURL

```bash
# 1. Generar presigned URL
curl -X POST http://localhost:8000/api/v1/documents/presigned \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "document_type": "CAMARA_COMERCIO",
    "file_name": "test.pdf",
    "mime_type": "application/pdf",
    "file_size_bytes": 1024000,
    "commerce_id": 5
  }'

# 2. Subir archivo a presigned URL (respuesta anterior)
curl -X PUT "https://minio:9000/documents?X-Amz-Algorithm=..." \
  -H "Content-Type: application/pdf" \
  --data-binary @/path/to/file.pdf

# 3. Confirmar carga
curl -X POST http://localhost:8000/api/v1/documents/confirm \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "upload_token": "550e8400-...",
    "s3_metadata": {
      "etag": "\"abc123...\"",
      "object_size": 1024000,
      "last_modified": "2026-01-19T14:30:00Z"
    }
  }'
```

---

## 10. Verificación

### ✅ Checklist de Setup

- [X] MinIO corriendo (`docker ps | grep minio`)
- [X] Bucket "documents" creado (`mc ls minio/`)
- [ ] `.env` configurado con credenciales MinIO
- [ ] Migración ejecutada (`php artisan migrate`)
- [ ] Rutas API registradas (`php artisan route:list | grep documents`)
- [ ] Controllador creado (`app/Http/Controllers/DocumentUploadController.php`)
- [ ] Servicio creado (`app/Services/DocumentUploadService.php`)
- [ ] Console de MinIO accesible (http://localhost:9001)

---

**Documento de Configuración:** 2026-01-19
