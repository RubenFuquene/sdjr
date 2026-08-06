<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

class PatchCommerceVerificationRequest extends FormRequest
{
    /**
     * SCRUM-334: exige admin.commerces.verify, no provider.commerces.update.
     * Verificar un comercio es una acción de la plataforma sobre un tercero —
     * el permiso de "actualizar mi comercio" (que el rol provider sí tiene)
     * permitía la auto-verificación, vaciando de sentido el estado
     * "verificado". Este permiso no se asigna al rol provider.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin.commerces.verify') ?? false;
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
