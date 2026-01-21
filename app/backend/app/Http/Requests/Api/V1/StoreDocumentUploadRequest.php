<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="StoreDocumentUploadRequest",
 *   type="object",
 *   required={"document_type", "file_name", "mime_type", "file_size_bytes", "commerce_id"},
 *   @OA\Property(property="document_type", type="string", example="ID_CARD"),
 *   @OA\Property(property="file_name", type="string", example="documento.pdf"),
 *   @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *   @OA\Property(property="file_size_bytes", type="integer", example=123456),
 *   @OA\Property(property="commerce_id", type="integer", example=1),
 *   @OA\Property(property="replace_document_id", type="integer", nullable=true, example=null),
 *   @OA\Property(property="versioning_enabled", type="boolean", example=true),
 *   @OA\Property(property="metadata", type="object", nullable=true, example={"key":"value"})
 * )
 */
class StoreDocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.providers.upload_documents') ?? false;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'in:' . implode(',', [
                Constant::DOCUMENT_TYPE_LICENSE,
                Constant::DOCUMENT_TYPE_OTHER,
                Constant::DOCUMENT_TYPE_CAMARA_COMERCIO,
                Constant::DOCUMENT_TYPE_RUT,
                Constant::DOCUMENT_TYPE_REGISTRATION,
                Constant::DOCUMENT_TYPE_ID_CARD,
            ])],
            'file_name' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'in:' . implode(',', Constant::ALLOWED_FILE_EXTENSIONS)],
            'file_size_bytes' => ['required', 'integer', 'min:1'],
            'commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'replace_document_id' => ['nullable', 'integer', 'exists:commerce_documents,id'],
            'versioning_enabled' => ['string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
