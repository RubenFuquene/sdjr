<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ShowProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.products.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
