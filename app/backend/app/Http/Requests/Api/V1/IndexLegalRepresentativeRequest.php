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
        ];
    }

    /**
     * Devuelve los filtros validados para el index.
     */
    public function validatedFilters(): array
    {
        return $this->only(['name', 'status']);
    }
}
