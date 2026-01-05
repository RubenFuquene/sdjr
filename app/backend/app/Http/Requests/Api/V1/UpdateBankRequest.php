<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="BankUpdateRequest",
 *     required={"name", "code"},
 *     @OA\Property(property="name", type="string", maxLength=100, example="Banco de Bogotá", description="Bank name"),
 *     @OA\Property(property="code", type="string", maxLength=20, example="BOGOTA123", description="Bank code (unique)"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)")
 * )
 */
class UpdateBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.banks.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:banks,code,' . $this->route('bank')],
            'status' => ['nullable', 'string', 'max:1'],
        ];
    }
}
