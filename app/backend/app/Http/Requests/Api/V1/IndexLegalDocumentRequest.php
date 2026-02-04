<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexLegalDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso: legal_documents.index
        return $this->user()?->can('legal_documents.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function validatedFilters(): array
    {
        return $this->only(['type', 'status']);
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    public function validatedPage(): int
    {
        return (int) ($this->input('page', 1));
    }
}
