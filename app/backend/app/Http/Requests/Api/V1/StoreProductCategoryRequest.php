<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\FiscalCode;
use App\Models\EstablishmentType;
use App\Services\FiscalCodeResolver;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @OA\Schema(
 *   schema="StoreProductCategoryRequest",
 *   required={"name"},
 *
 *   @OA\Property(property="name", type="string", maxLength=100, example="Bebidas", description="Category name"),
 *   @OA\Property(property="description", type="string", maxLength=255, nullable=true, example="Categoría de bebidas", description="Category description"),
 *   @OA\Property(property="establishment_type_id", type="integer", nullable=true, example=1, description="Establishment type id"),
 *   @OA\Property(property="default_fiscal_code", type="string", nullable=true, example="iva_19_general", description="Sugerencia de código fiscal para productos de esta categoría (SCRUM-362)"),
 *   @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)"),
 * )
 */
class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.product_categories.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'establishment_type_id' => ['nullable', 'integer', 'exists:establishment_types,id'],
            'default_fiscal_code' => ['nullable', new Enum(FiscalCode::class)],
            'name' => ['required', 'string', 'max:100'],
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
     * SCRUM-362 (3.6): una categoría de Retail no puede sugerir Impoconsumo
     * — el default debe estar dentro de lo que su propio establishment_type
     * permite. Sin establishment_type_id, la categoría es genérica y no hay
     * tipo contra el cual validar.
     */
    private function validateDefaultFiscalCodeCoherence(Validator $validator): void
    {
        $defaultFiscalCode = $this->input('default_fiscal_code');
        $establishmentTypeId = $this->input('establishment_type_id');

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
