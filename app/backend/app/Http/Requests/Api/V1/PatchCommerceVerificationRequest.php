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
            'is_verified' => ['required', 'integer', 'in:'.Constant::COMMERCE_PENDING.','.Constant::COMMERCE_VERIFIED.','.Constant::COMMERCE_REJECTED],
            'message' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'message.required' => 'The custom message field is required.',
            'message.string' => 'The message must be a string.',
            'message.min' => 'The message must be at least 10 characters.',
            'message.max' => 'The message may not be greater than 500 characters.',
        ];
    }
}
