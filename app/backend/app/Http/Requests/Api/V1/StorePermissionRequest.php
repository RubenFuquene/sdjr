<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StorePermissionRequest",
 *     required={"name", "description"},
 *
 *     @OA\Property(property="name", type="string", maxLength=50, example="users.create", description="Permission name (unique)"),
 *     @OA\Property(property="description", type="string", maxLength=255, example="Permite crear usuarios", description="Permission description")
 * )
 */
class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.permissions.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'unique:permissions,name'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }
}
