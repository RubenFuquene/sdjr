<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PermissionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('permissions.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:permissions,name'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }
}
