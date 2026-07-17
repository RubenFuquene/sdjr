<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="StoreDocumentUploadRequest",
 *   type="object",
 *   required={"document_type", "file_name", "mime_type", "file_size_bytes", "commerce_id"},
 *
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
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->can('admin.providers.documents.manage')) {
            return true;
        }

        if (! $user->can('provider.documents.upload')) {
            return false;
        }

        return $this->userCanAccessCommerce((int) $this->input('commerce_id'));
    }

    public function rules(): array
    {
        return [
            'document_type' => ['sometimes', 'string', 'in:'.implode(',', [
                Constant::DOCUMENT_TYPE_LICENSE,
                Constant::DOCUMENT_TYPE_OTHER,
                Constant::DOCUMENT_TYPE_CAMARA_COMERCIO,
                Constant::DOCUMENT_TYPE_RUT,
                Constant::DOCUMENT_TYPE_REGISTRATION,
                Constant::DOCUMENT_TYPE_ID_CARD,
                Constant::DOCUMENT_TYPE_1876,
            ]), function ($attribute, $value, $fail) {
                if ($value !== Constant::DOCUMENT_TYPE_1876) {
                    return;
                }

                $commerce = Commerce::find($this->input('commerce_id'));

                if (! $commerce || ! $commerce->electronic_invoicing_required) {
                    $fail('El formato 1876 solo aplica para comercios obligados a facturar electrónicamente.');
                }
            }],
            'file_name' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'in:'.implode(',', Constant::ALLOWED_FILE_EXTENSIONS)],
            'file_size_bytes' => ['required', 'integer', 'min:1', 'max:'.Constant::ALLOWED_SIZE_BYTES],
            'commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'replace_document_id' => ['nullable', 'integer', 'exists:commerce_documents,id'],
            'versioning_enabled' => ['string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
