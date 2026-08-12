<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\FiscalCode;
use App\Models\EstablishmentType;
use App\Models\ProductCategory;
use App\Services\FiscalCodeResolver;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @OA\Schema(
 *   schema="UpdateProductCategoryRequest",
 *
 *   @OA\Property(property="name", type="string", maxLength=100, example="Bebidas", description="Category name"),
 *   @OA\Property(property="description", type="string", maxLength=255, nullable=true, example="Categoría de bebidas", description="Category description"),
 *   @OA\Property(property="establishment_type_id", type="integer", nullable=true, example=1, description="Establishment type id"),
 *   @OA\Property(property="default_fiscal_code", type="string", nullable=true, example="iva_19_general", description="Sugerencia de código fiscal para productos de esta categoría (SCRUM-362)"),
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
            'default_fiscal_code' => ['nullable', new Enum(FiscalCode::class)],
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'max:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateDefaultFiscalCodeCoherence($validator);
        });
    }

    /**
     * SCRUM-362 (3.6): valor efectivo (payload ?? BD) para ambos campos —
     * una edición que solo toca el nombre no debe dejar de validar contra
     * el establishment_type_id ya guardado.
     */
    private function validateDefaultFiscalCodeCoherence(Validator $validator): void
    {
        $categoryId = (int) ($this->route('id') ?? $this->route('product_category') ?? 0);
        $existingCategory = $categoryId ? ProductCategory::find($categoryId) : null;

        $defaultFiscalCode = $this->has('default_fiscal_code')
            ? $this->input('default_fiscal_code')
            : $existingCategory?->default_fiscal_code?->value;

        $establishmentTypeId = $this->has('establishment_type_id')
            ? $this->input('establishment_type_id')
            : $existingCategory?->establishment_type_id;

        if (! $defaultFiscalCode || ! $establishmentTypeId) {
            return;
        }

        $fiscalCodeEnum = FiscalCode::tryFrom($defaultFiscalCode);
        $establishmentType = EstablishmentType::find($establishmentTypeId);

        if (! $fiscalCodeEnum || ! $establishmentType) {
            return;
        }

        $allowed = app(FiscalCodeResolver::class)->allowedForEstablishmentType($establishmentType);

        if (! in_array($fiscalCodeEnum, $allowed, true)) {
            $validator->errors()->add(
                'default_fiscal_code',
                'This default_fiscal_code is not allowed for the category establishment type.'
            );
        }
    }
}
