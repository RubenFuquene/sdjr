<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UpdateProductCategoryRequest",
 *
 *   @OA\Property(property="name", type="string", maxLength=100, example="Bebidas", description="Category name"),
 *   @OA\Property(property="description", type="string", maxLength=255, nullable=true, example="Categoría de bebidas", description="Category description"),
 *   @OA\Property(property="establishment_type_id", type="integer", nullable=true, example=1, description="Establishment type id"),
 *   @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)"),
 * )
 */
class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.product_categories.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'establishment_type_id' => ['nullable', 'integer', 'exists:establishment_types,id'],
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:1'],
        ];
    }
}
