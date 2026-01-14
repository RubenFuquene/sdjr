<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IndexCategoryRequest
 *
 * @OA\Schema(
 *     schema="IndexCategoryRequest",
 *     type="object",
 *
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         minimum=1,
 *         maximum=100,
 *         description="Cantidad de registros por página (default: 15)",
 *         example=10
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"1", "0", "all"},
 *         description="Filtrar por estado: 1 (activo), 0 (inactivo), all (todos)",
 *         example="1"
 *     )
 * )
 */
class IndexCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.categories.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'in:1,0,all'],
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
        return $this->only(['name', 'status']);
    }
}
