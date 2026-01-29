<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ShowProductPackageItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product_package_items.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
