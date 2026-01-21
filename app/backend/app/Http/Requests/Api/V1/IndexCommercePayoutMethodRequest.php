<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexCommercePayoutMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso: commerce_payout_methods.index
        return $this->user()?->can('provider.commerce_payout_methods.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'type' => ['sometimes', 'string'],
            'owner_id' => ['sometimes', 'integer'],
            'account_number' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string', 'max:1'],
        ];
    }

    public function validatedFilters(): array
    {
        return $this->only(['type', 'owner_id', 'account_number', 'status']);
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
