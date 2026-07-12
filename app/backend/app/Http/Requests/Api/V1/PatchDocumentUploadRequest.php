<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\CommerceDocument;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="PatchDocumentUploadRequest",
 *   type="object",
 *   required={"upload_token", "s3_metadata"},
 *
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
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->can('admin.providers.upload_documents')) {
            return true;
        }

        if (! $user->hasAnyPermission(['provider.documents.upload', 'provider.photos.upload'])) {
            return false;
        }

        $document = CommerceDocument::where('upload_token', $this->input('upload_token'))->first();

        if (! $document) {
            // Sin documento aún resuelto: el controller responde 404 (ModelNotFoundException), no 403.
            return true;
        }

        return $this->userCanAccessCommerce((int) $document->commerce_id);
    }

    public function rules(): array
    {
        return [
            'upload_token' => ['required', 'string'],
            's3_metadata' => ['required', 'array'],
            's3_metadata.etag' => ['required', 'string'],
            's3_metadata.object_size' => ['required', 'integer', 'min:1'],
            's3_metadata.last_modified' => ['required', 'string'],
        ];
    }
}
