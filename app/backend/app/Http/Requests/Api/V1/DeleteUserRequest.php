<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="DeleteUserRequest",
 *     description="Request for deleting a user",
 *
 *     @OA\Property(property="id", type="integer", description="User ID")
 * )
 */
class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para eliminar un usuario
        return $this->user()?->can('admin.profiles.users.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
