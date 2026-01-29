<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RoleAssignPermissionRequest",
 *     required={"permissions"},
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
 *         description="If true, replaces all permissions; if false, adds"
 *     )
 * )
 */
class RoleAssignPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.roles.assign_permissions') ?? false;
    }

    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
