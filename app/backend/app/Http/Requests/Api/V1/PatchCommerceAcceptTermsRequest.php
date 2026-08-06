<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

class PatchCommerceAcceptTermsRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    /**
     * Autoriza solo si el usuario está autenticado, tiene el permiso correcto
     * y el comercio le pertenece — aceptar términos es una acción del dueño.
     */
    public function authorize(): bool
    {
        if (! $this->user()?->can('provider.commerces.accept-terms')) {
            return false;
        }

        $commerceId = (int) ($this->route('commerce_id') ?? $this->route('id') ?? $this->route('commerce') ?? 0);

        return $this->userCanAccessCommerce($commerceId);
    }

    /**
     * Reglas de validación para la aceptación de términos del comercio.
     */
    public function rules(): array
    {
        return [
            'terms_accepted_version' => ['required', 'integer', 'min:1'],
        ];
    }
}
