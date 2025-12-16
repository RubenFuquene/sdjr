<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CommerceBasicDataRequest",
 *     @OA\Property(property="commerce", ref="#/components/schemas/CommerceRequest"),
 *     @OA\Property(property="legal_representatives", type="array", @OA\Items(ref="#/components/schemas/LegalRepresentativeRequest")),
 *     @OA\Property(property="commerce_documents", type="array", @OA\Items(ref="#/components/schemas/CommerceDocument"))
 * )
 */
class CommerceBasicDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('commerces.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'commerce' => ['nullable', 'array'],
            'commerce.*.owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'commerce.*.department_id' => ['required', 'integer', 'exists:departments,id'],
            'commerce.*.city_id' => ['required', 'integer', 'exists:cities,id'],
            'commerce.*.neighborhood_id' => ['required', 'integer', 'exists:neighborhoods,id'],
            'commerce.*.name' => ['required', 'string', 'max:255'],
            'commerce.*.description' => ['nullable', 'string', 'max:500'],
            'commerce.*.tax_id' => ['required', 'string', 'max:50'],
            'commerce.*.tax_id_type' => ['required', 'string', 'max:10'],
            'commerce.*.address' => ['required', 'string', 'max:255'],
            'commerce.*.phone' => ['nullable', 'string', 'max:20'],
            'commerce.*.email' => ['nullable', 'string', 'email', 'max:100'],
            'commerce.*.is_verified' => ['boolean'],
            'commerce.*.is_active' => ['boolean'],

            'legal_representatives' => ['nullable', 'array'],
            'legal_representatives.*.name' => ['required_with:legal_representatives', 'string', 'max:255'],
            'legal_representatives.*.last_name' => ['required_with:legal_representatives', 'string', 'max:255'],
            'legal_representatives.*.document' => ['required_with:legal_representatives', 'string', 'max:30'],
            'legal_representatives.*.document_type' => ['required_with:legal_representatives', 'string', 'in:CC,CE,NIT,PAS'],
            'legal_representatives.*.email' => ['nullable', 'string', 'email', 'max:100'],
            'legal_representatives.*.phone' => ['nullable', 'string', 'max:20'],
            'legal_representatives.*.is_primary' => ['boolean'],

            'commerce_documents' => ['nullable', 'array'],
            'commerce_documents.*.verified_by_id' => ['nullable', 'integer', 'exists:users,id'],
            'commerce_documents.*.uploaded_by_id' => ['nullable', 'integer', 'exists:users,id'],
            'commerce_documents.*.document_type' => ['nullable', 'string', 'max:100'],
            'commerce_documents.*.file_path' => ['nullable', 'string', 'max:255'],
            'commerce_documents.*.mime_type' => ['nullable', 'string', 'max:100'],
            'commerce_documents.*.verified' => ['boolean'],
            'commerce_documents.*.uploaded_at' => ['nullable', 'date'],
            'commerce_documents.*.verified_at' => ['nullable', 'date'],
        ];
    }
}
