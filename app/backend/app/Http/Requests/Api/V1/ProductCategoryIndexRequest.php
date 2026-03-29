<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="ProductCategoryIndexRequest",
 *   title="ProductCategoryIndexRequest",
 *   description="Request schema for listing product categories",
 *   type="object",
 *
 *   @OA\Property(property="per_page", type="integer", description="Items per page (default: 15, max: 100)", minimum=1, maximum=100, example=15),
 *   @OA\Property(property="establishment_type_id", type="integer", description="Establishment type ID", example=2),
 *   @OA\Property(property="status", type="string", description="Status (1=active, 0=inactive)", maxLength=1, example="1"),
 *   @OA\Property(property="name", type="string", description="Category name", maxLength=100, example="Bebidas"),
 *   @OA\Property(property="description", type="string", description="Category description", maxLength=255, example="Categoría de bebidas alcohólicas y no alcohólicas")
 * )
 */
class ProductCategoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.product_categories.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'establishment_type_id' => ['sometimes', 'integer', 'exists:establishment_types,id'],
            'status' => ['sometimes', 'string', 'max:1'],
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    public function validatedStatus(): ?string
    {
        return $this->input('status');
    }
}
