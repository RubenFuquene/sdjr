<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UserIndexRequest
 *
 * @OA\Schema(
 *     schema="UserIndexRequest",
 *     type="object",
 *
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         minimum=1,
 *         maximum=100,
 *         description="Cantidad de registros por página (default: 15)",
 *         example=10
 *     )
 * )
 */
class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.profiles.users.index') ?? false;
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
     *
     * @return array
     */
    public function validatedFilters(): array
    {
        return $this->only(['name', 'last_name', 'phone', 'email', 'status']);
    }
}
