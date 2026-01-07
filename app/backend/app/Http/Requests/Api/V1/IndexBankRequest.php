<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.banks.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:20'],
            'status' => ['sometimes', 'string', 'size:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function validatedPerPage(): int
    {
        return $this->input('per_page', 15);
    }
}
