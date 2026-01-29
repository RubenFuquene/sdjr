<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UserAssignRolePermissionRequest",
 *
 *     @OA\Property(
 *         property="roles",
 *         type="array",
 *         description="Array of role names to assign",
 *
 *         @OA\Items(type="string", example="admin")
 *     ),
 *
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         description="Array of permission names to assign",
 *
 *         @OA\Items(type="string", example="users.create")
 *     ),
 *
 *     @OA\Property(
 *         property="sync",
 *         type="boolean",
 *         example=true,
 *         description="If true, replaces all roles/permissions; if false, adds"
 *     )
 * )
 */
class UserAssignRolePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.users.assign_roles_permissions') ?? false;
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
