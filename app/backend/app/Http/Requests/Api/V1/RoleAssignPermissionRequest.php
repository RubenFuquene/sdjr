<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class RoleAssignPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles.assign_permissions') ?? false;
    }

    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
