<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\LegalRepresentative;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-334: `commerce_id` es requerido solo al crear. En update no se acepta
 * del cliente — el comercio dueño se deriva del representante existente
 * (mismo criterio aplicado a UpdateCommerceBranchRequest en SCRUM-287).
 *
 * @OA\Schema(
 *     schema="LegalRepresentativeRequest",
 *     required={"name","last_name","document","document_type"},
 *
 *     @OA\Property(property="commerce_id", type="integer", example=1, description="Requerido solo al crear"),
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
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $action = $this->route()->getActionMethod();
        $permission = 'provider.legal_representatives.'.($action === 'store' ? 'create' : 'update');

        if (! $user->can($permission)) {
            return false;
        }

        if ($action === 'store') {
            return $this->userCanAccessCommerce((int) $this->input('commerce_id'));
        }

        $legalRepresentativeId = (int) ($this->route('legal_representative') ?? 0);
        if ($legalRepresentativeId <= 0) {
            return false;
        }

        $commerceId = LegalRepresentative::query()->whereKey($legalRepresentativeId)->value('commerce_id');

        return $commerceId !== null && $this->userCanAccessCommerce((int) $commerceId);
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:30'],
            'document_type' => ['required', 'string', 'in:CC,CE,NIT,PAS'],
            'email' => ['nullable', 'string', 'max:100', 'email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_primary' => ['boolean'],
        ];

        if ($this->route()->getActionMethod() === 'store') {
            $rules['commerce_id'] = ['required', 'integer', 'exists:commerces,id'];
        }

        return $rules;
    }
}
