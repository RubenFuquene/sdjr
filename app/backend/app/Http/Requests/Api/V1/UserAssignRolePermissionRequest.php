<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UserAssignRolePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.assign_roles_permissions') ?? false;
    }

    public function rules(): array
    {
        return [
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
