<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexPqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.pqrs_types.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', 'size:1'],
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:20'],
        ];
    }

    public function validatedPerPage(): int
    {
        return intval($this->input('per_page', 15));
    }

    public function validatedStatus(): ?string
    {
        return intval($this->input('status')) ?: null;
    }
}
