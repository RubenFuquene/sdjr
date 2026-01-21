<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="PatchDocumentUploadRequest",
 *   type="object",
 *   required={"upload_token", "s3_metadata"},
 *   @OA\Property(property="upload_token", type="string", example="token123"),
 *   @OA\Property(
 *     property="s3_metadata",
 *     type="object",
 *     required={"etag", "object_size", "last_modified"},
 *     @OA\Property(property="etag", type="string", example="etag123"),
 *     @OA\Property(property="object_size", type="integer", example=123456),
 *     @OA\Property(property="last_modified", type="string", example="2026-01-21T10:00:00Z")
 *   )
 * )
 */
class PatchDocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.providers.upload_documents') ?? false;
    }

    public function rules(): array
    {
        return [
            'upload_token' => ['required', 'string', 'exists:commerce_documents,upload_token'],
            's3_metadata' => ['required', 'array'],
            's3_metadata.etag' => ['required', 'string'],
            's3_metadata.object_size' => ['required', 'integer', 'min:1'],
            's3_metadata.last_modified' => ['required', 'string'],
        ];
    }
}
