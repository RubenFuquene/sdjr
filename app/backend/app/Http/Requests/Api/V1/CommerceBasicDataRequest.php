<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CommerceBasicDataRequest
 *
 * @OA\Schema(
 *     schema="CommerceBasicDataRequest",
 *     type="object",
 *     description="Request schema for basic commerce onboarding data, including commerce and legal representative. Documents (RUT, 1876, etc.) are uploaded separately via the presigned upload flow once the commerce exists.",
 *     required={"commerce","legal_representative"},
 *
 *     @OA\Property(
 *         property="commerce",
 *         type="object",
 *         required={"owner_user_id","department_id","city_id","neighborhood_id","establishment_type_id","name","tax_id","tax_id_type","address"},
 *         @OA\Property(property="owner_user_id", type="integer", example=1),
 *         @OA\Property(property="department_id", type="integer", example=1),
 *         @OA\Property(property="city_id", type="integer", example=1),
 *         @OA\Property(property="neighborhood_id", type="integer", example=1),
 *         @OA\Property(property="establishment_type_id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", maxLength=255, example="Acme Market"),
 *         @OA\Property(property="description", type="string", maxLength=500, example="Retail commerce"),
 *         @OA\Property(property="tax_id", type="string", maxLength=50, example="900123456"),
 *         @OA\Property(property="tax_id_type", type="string", enum={"NIT","CC","PS","CE"}, example="NIT"),
 *         @OA\Property(property="address", type="string", maxLength=255, example="123 Main St"),
 *         @OA\Property(property="phone", type="string", maxLength=20, example="3001234567"),
 *         @OA\Property(property="email", type="string", format="email", maxLength=100, example="info@acme.com"),
 *         @OA\Property(property="is_verified", type="boolean", example=false),
 *         @OA\Property(property="is_active", type="boolean", example=true),
 *         @OA\Property(property="electronic_invoicing_required", type="boolean", example=false, description="Autodeclaración: si el comercio está obligado a emitir factura electrónica. Determina si se exige el formato 1876.")
 *     ),
 *     @OA\Property(
 *         property="legal_representative",
 *         type="object",
 *         required={"name","last_name","document","document_type"},
 *         @OA\Property(property="name", type="string", maxLength=255, example="John"),
 *         @OA\Property(property="last_name", type="string", maxLength=255, example="Doe"),
 *         @OA\Property(property="document", type="string", maxLength=30, example="123456789"),
 *         @OA\Property(property="document_type", type="string", enum={"CC","CE","NIT","PAS"}, example="CC"),
 *         @OA\Property(property="email", type="string", format="email", maxLength=100, example="john.doe@example.com"),
 *         @OA\Property(property="phone", type="string", maxLength=20, example="3007654321"),
 *         @OA\Property(property="is_primary", type="boolean", example=true)
 *     )
 * )
 */
class CommerceBasicDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.commerces.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'commerce' => ['nullable', 'array'],
            'commerce.owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'commerce.department_id' => ['required', 'integer', 'exists:departments,id'],
            'commerce.city_id' => ['required', 'integer', 'exists:cities,id'],
            'commerce.neighborhood_id' => ['required', 'integer', 'exists:neighborhoods,id'],
            'commerce.establishment_type_id' => ['required', 'integer', 'exists:establishment_types,id'],
            'commerce.name' => ['required', 'string', 'max:255'],
            'commerce.description' => ['nullable', 'string', 'max:500'],
            'commerce.tax_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('commerces', 'tax_id')->where(function ($query) {
                    return $query->where('tax_id_type', $this->input('commerce.tax_id_type'));
                }),
            ],
            'commerce.tax_id_type' => ['required', 'string', 'max:10', Rule::in([
                Constant::COMMERCE_DOCUMENT_TYPE_NIT,
                Constant::COMMERCE_DOCUMENT_TYPE_CC,
                Constant::COMMERCE_DOCUMENT_TYPE_PS,
                Constant::COMMERCE_DOCUMENT_TYPE_CE,
            ])],
            'commerce.address' => ['required', 'string', 'max:255'],
            'commerce.phone' => ['nullable', 'string', 'max:20'],
            'commerce.email' => ['nullable', 'string', 'email', 'max:100'],
            'commerce.is_verified' => ['boolean'],
            'commerce.is_active' => ['boolean'],
            'commerce.electronic_invoicing_required' => ['required', 'boolean'],

            'legal_representative' => ['nullable', 'array'],
            'legal_representative.name' => ['required', 'string', 'max:255'],
            'legal_representative.last_name' => ['required', 'string', 'max:255'],
            'legal_representative.document' => ['required', 'string', 'max:30'],
            'legal_representative.document_type' => ['required', 'string', 'in:CC,CE,NIT,PAS'],
            'legal_representative.email' => ['nullable', 'string', 'email', 'max:100'],
            'legal_representative.phone' => ['nullable', 'string', 'max:20'],
            'legal_representative.is_primary' => ['boolean'],

            // 'my_account.type' => ['required', 'string', 'max:15'],
            // 'my_account.account_type' => ['required', 'string', 'max:50'],
            // 'my_account.bank_id' => ['required', 'exists:banks,id'],
            // 'my_account.account_number' => ['required', 'string', 'max:50'],
            // 'my_account.owner_id' => ['required', 'exists:users,id'],
            // 'my_account.is_primary' => ['boolean'],
        ];
    }

    /**
     * Get custom validation messages for commerce uniqueness rule.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'commerce.tax_id.unique' => 'A commerce with the same tax ID and document type already exists.',
            'commerce.name.unique' => 'There is already a commerce registered with the same name, owner, and address.',
        ];
    }
}
