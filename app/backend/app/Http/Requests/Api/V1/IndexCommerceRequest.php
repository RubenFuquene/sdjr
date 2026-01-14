<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCommerceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('provider.commerces.index') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['1', '0', 'all'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get validated perPage value or default.
     */
    public function validatedPerPage(): int
    {
        return (int) $this->input('per_page') ?? 15;
    }

    /**
     * Get validated page value or default.
     */
    public function validatedPage(): int
    {
        return (int) $this->input('page') ?? 1;
    }

    /**
     * Get validated filters.
     */
    public function validatedFilters(): array
    {
        return $this->only(['search', 'status']);
    }
}
