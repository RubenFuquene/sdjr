<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShowAdministratorRequest
 *
 * @OA\Schema(
 *     schema="ShowAdministratorRequest",
 *     type="object",
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del usuario administrador",
 *         example=1
 *     )
 * )
 */
class ShowAdministratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.administrators.show') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    /**
     * Get validated filters.
     */
    public function validatedFilters(): array
    {
        return $this->only(['name', 'last_name', 'email', 'status']);
    }
}
