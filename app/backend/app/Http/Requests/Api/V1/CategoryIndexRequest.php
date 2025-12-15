<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CategoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'in:1,0,all'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int)($this->input('per_page', 15));
    }

    public function validatedStatus(): string
    {
        return $this->input('status', 'all');
    }
}
