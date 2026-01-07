<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePriorityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.priority_types.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:priority_types,code'],
            'status' => ['sometimes', 'max:1'],
        ];
    }
}
