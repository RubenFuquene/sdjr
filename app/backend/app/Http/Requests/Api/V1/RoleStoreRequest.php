<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RoleStoreRequest",
 *     required={"name", "description"},
 *     @OA\Property(property="name", type="string", maxLength=50, example="admin", description="Role name (unique)"),
 *     @OA\Property(property="description", type="string", maxLength=255, example="Administrador del sistema", description="Role description"),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         description="Array of permission names",
 *         @OA\Items(type="string", example="users.create")
 *     )
 * )
 */
class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.roles.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'description' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
