<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

class PatchCommerceVerificationRequest extends FormRequest
{
    /**
     * Autoriza la petición solo si el usuario tiene el permiso provider.commerces.update
     */
    public function authorize(): bool
    {
        return $this->user()?->can('provider.commerces.update') ?? false;
    }

    /**
     * Reglas de validación para la verificación del comercio.
     */
    public function rules(): array
    {
        return [
            'is_verified' => ['required', 'integer', 'in:'.Constant::STATUS_ACTIVE.','.Constant::STATUS_INACTIVE],
        ];
    }
}
