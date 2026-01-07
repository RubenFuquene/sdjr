<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePqrsTypeRequest
 *
 * @OA\Schema(
 *     schema="StorePqrsTypeRequest",
 *     type="object",
 *     required={"name", "code"},
 *
 *     @OA\Property(property="name", type="string", maxLength=100, example="Petición"),
 *     @OA\Property(property="code", type="string", maxLength=20, example="PQRS01"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="1=Activo, 0=Inactivo")
 * )
 */
class StorePqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.pqrs_types.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:pqrs_types,code'],
            'status' => ['sometimes', 'size:1'],
        ];
    }
}
