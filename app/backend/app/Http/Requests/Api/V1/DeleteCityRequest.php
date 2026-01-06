<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DeleteCityRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin.cities.delete') ?? false;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
