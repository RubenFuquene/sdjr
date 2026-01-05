<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.pqrs_types.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:20', 'unique:pqrs_types,code,' . $this->route('pqrs_type')],
            'status' => ['sometimes', 'size:1'],
        ];
    }
}
