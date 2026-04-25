<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.products.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'string', 'max:1'],
            'title' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string', 'max:255'],
            'commerce_id' => ['sometimes', 'integer', 'exists:commerces,id'],
            'product_category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'sort_by' => ['sometimes', 'string', 'in:title,status,created_at,updated_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    public function validatedStatus(): ?string
    {
        return $this->input('status');
    }
}
