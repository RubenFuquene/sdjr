<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowRoleRequest",
 *     description="Request for showing a role",
 *     @OA\Property(property="id", type="integer", description="Role ID")
 * )
 */
class ShowRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para ver un rol
        return $this->user()?->can('admin.profiles.roles.show') ?? false;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}
