<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowUserRequest",
 *     description="Request for showing a user",
 *
 *     @OA\Property(property="id", type="integer", description="User ID")
 * )
 */
class ShowUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para ver un usuario
        return $this->user()?->can('admin.profiles.users.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
