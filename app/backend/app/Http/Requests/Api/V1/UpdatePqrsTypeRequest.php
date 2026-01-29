<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdatePqrsTypeRequest
 *
 * @OA\Schema(
 *     schema="UpdatePqrsTypeRequest",
 *     type="object",
 *
 *     @OA\Property(property="name", type="string", maxLength=100, example="Petición"),
 *     @OA\Property(property="code", type="string", maxLength=20, example="PQRS01"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="1=Activo, 0=Inactivo")
 * )
 */
class UpdatePqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.pqrs_types.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:20', 'unique:pqrs_types,code,'.$this->route('pqrs_type')],
            'status' => ['sometimes', 'size:1'],
        ];
    }
}
