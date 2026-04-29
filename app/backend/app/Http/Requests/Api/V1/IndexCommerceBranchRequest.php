<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexCommerceBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.branches.show') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'name' => ['sometimes', 'string', 'max:100'],
            'address' => ['sometimes', 'string', 'max:255'],
            'longitude' => ['sometimes', 'numeric'],
            'latitude' => ['sometimes', 'numeric'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'email' => ['sometimes', 'email', 'max:255'],
            'status' => ['sometimes', 'string', 'max:1'],
            'sort_by' => ['sometimes', 'string', 'in:name,address,status,created_at,updated_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    /**
     * Get validated filters.
     */
    public function validatedFilters(): array
    {
        return $this->only(['name', 'address', 'longitude', 'latitude', 'phone', 'email', 'status', 'sort_by', 'sort_dir']);
    }
}
