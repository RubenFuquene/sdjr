<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

class PatchRoleStatusRequest extends FormRequest
{
    /**
     * Autoriza la petición solo si el usuario tiene el permiso admin.profiles.roles.update
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.roles.update') ?? false;
    }

    /**
     * Reglas de validación para el estado del rol.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'integer', 'in:' . Constant::STATUS_ACTIVE . ',' . Constant::STATUS_INACTIVE],
        ];
    }
}
