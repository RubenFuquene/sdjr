<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-362 (CA-09): reporte interno de productos sin clasificar
 * (fiscal_code = otro_verificar). Ruta de administración, protegida por
 * permiso explícito — no por oscuridad de URL.
 */
class IndexPendingFiscalClassificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.products.fiscal_review.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'commerce_id' => ['sometimes', 'integer', 'exists:commerces,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) $this->input('per_page', 15);
    }
}
