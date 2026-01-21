<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CommerceBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.providers.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'commerce_id' => ['required', 'integer', 'exists:commerces,id'],
        ];
    }
}
