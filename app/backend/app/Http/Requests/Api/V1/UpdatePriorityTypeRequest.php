<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdatePriorityTypeRequest
 *
 * @OA\Schema(
 *     schema="UpdatePriorityTypeRequest",
 *     type="object",
 *
 *     @OA\Property(property="name", type="string", maxLength=100, example="Alta"),
 *     @OA\Property(property="code", type="string", maxLength=50, example="ALTA"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="1=Activo, 0=Inactivo")
 * )
 */
class UpdatePriorityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.priority_types.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:priority_types,code,'.$this->route('id')],
            'status' => ['sometimes', 'string', 'max:1'],
        ];
    }
}
