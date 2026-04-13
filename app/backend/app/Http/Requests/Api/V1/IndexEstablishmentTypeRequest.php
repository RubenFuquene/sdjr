<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="IndexEstablishmentTypeRequest",
 *     description="Request for listing establishment types",
 *
 *     @OA\Property(property="name", type="string", description="Filter by establishment type name"),
 *     @OA\Property(property="status", type="string", description="Filter by status")
 * )
 */
class IndexEstablishmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.establishments.index') ?? false;
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
