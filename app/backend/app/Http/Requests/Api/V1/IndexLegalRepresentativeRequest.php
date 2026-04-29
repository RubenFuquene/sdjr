<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="IndexLegalRepresentativeRequest",
 *     description="Request for listing legal representatives",
 *
 *     @OA\Property(property="name", type="string", description="Filter by name"),
 *     @OA\Property(property="status", type="string", description="Filter by status")
 * )
 */
class IndexLegalRepresentativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para listar representantes legales
        return $this->user()?->can('provider.legal_representatives.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', 'in:name,status,created_at,updated_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    /**
     * Devuelve los filtros validados para el index.
     */
    public function validatedFilters(): array
    {
        return $this->only(['name', 'status', 'sort_by', 'sort_dir']);
    }
}
