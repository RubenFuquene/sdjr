<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IndexPqrsTypeRequest
 *
 * @OA\Schema(
 *     schema="IndexPqrsTypeRequest",
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
 *         description="Estado del tipo PQRS (1=activo, 0=inactivo)",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=100,
 *         description="Nombre del tipo PQRS para filtrar.",
 *         example="Petición"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         maxLength=20,
 *         description="Código del tipo PQRS para filtrar.",
 *         example="PQRS01"
 *     )
 * )
 */
class IndexPqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.pqrs_types.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', 'size:1'],
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:20'],
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
        return $this->only(['name', 'code', 'status']);
    }
}
