<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexSupportStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.support_statuses.index') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'   => ['nullable', 'string', 'max:100'],
            'code'   => ['nullable', 'string', 'max:20'],
            'color'  => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', Rule::in(['1', '0', 'all'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get validated perPage value or default.
     *
     * @return int
     */
    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }
    
    /**
     * Get validated filters.
     *
     * @return array
     */
    public function validatedFilters(): array
    {
        return $this->only(['name', 'code', 'color', 'status']);
    }
}
