<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.pqrs_types.create') ?? false;
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
