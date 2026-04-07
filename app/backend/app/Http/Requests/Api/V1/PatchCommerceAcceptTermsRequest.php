<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PatchCommerceAcceptTermsRequest extends FormRequest
{
    /**
     * Autoriza solo si el usuario está autenticado y tiene el permiso correcto
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('provider.commerces.accept-terms');
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
