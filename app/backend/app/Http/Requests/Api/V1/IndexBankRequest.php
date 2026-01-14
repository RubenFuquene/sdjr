<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IndexBankRequest
 *
 * @OA\Schema(
 *     schema="IndexBankRequest",
 *     type="object",
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=100,
 *         description="Nombre del banco para filtrar.",
 *         example="Banco Ejemplo"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         maxLength=20,
 *         description="Código del banco para filtrar.",
 *         example="1234"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Estado del banco (1=activo, 0=inactivo)",
 *         example="1"
 *     ),
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
class IndexBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.banks.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:20'],
            'status' => ['sometimes', 'string', 'size:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
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
        return $this->only(['name', 'code', 'status']);
    }
}
