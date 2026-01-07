<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IndexPriorityTypeRequest
 *
 * @OA\Schema(
 *     schema="IndexPriorityTypeRequest",
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
 *         maxLength=1,
 *         description="Estado del tipo de prioridad (1=activo, 0=inactivo)",
 *         example="1"
 *     )
 * )
 */
class IndexPriorityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.priority_types.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', 'max:1'],
        ];
    }

    public function validatedPerPage(): int
    {
        return intval($this->validated('per_page', 15));
    }
}
