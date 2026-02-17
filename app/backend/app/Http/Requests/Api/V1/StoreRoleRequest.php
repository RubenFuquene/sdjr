<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="StoreRoleRequest",
 *     required={"name", "description"},
 *
 *     @OA\Property(property="name", type="string", maxLength=50, example="admin", description="Role name (unique)"),
 *     @OA\Property(property="description", type="string", maxLength=255, example="Administrador del sistema", description="Role description"),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         description="Array of permission names",
 *
 *         @OA\Items(type="string", example="users.create")
 *     )
 * )
 */
class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.roles.create') ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'description' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];

        // Para actualizaciones, excluir el registro actual de la validación de unicidad
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($this->route('id'))];
        } else {
            $rules['name'] = ['required', 'string', 'max:50', 'unique:roles,name'];
        }

        return $rules;
    }
}
