<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LegalRepresentativeRequest",
 *     required={"commerce_id","name","last_name","document","document_type"},
 *     @OA\Property(property="commerce_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", maxLength=255, example="Juan"),
 *     @OA\Property(property="last_name", type="string", maxLength=255, example="Pérez"),
 *     @OA\Property(property="document", type="string", maxLength=30, example="1234567890"),
 *     @OA\Property(property="document_type", type="string", enum={"CC","CE","NIT","PAS"}, example="CC"),
 *     @OA\Property(property="email", type="string", maxLength=100, example="juan.perez@example.com"),
 *     @OA\Property(property="phone", type="string", maxLength=20, example="3001234567"),
 *     @OA\Property(property="is_primary", type="boolean", example=true)
 * )
 */
class LegalRepresentativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'legal_representatives.' . ($action === 'store' ? 'create' : 'update');
        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:30'],
            'document_type' => ['required', 'string', 'in:CC,CE,NIT,PAS'],
            'email' => ['nullable', 'string', 'max:100', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_primary' => ['boolean'],
        ];
        return $rules;
    }
}
