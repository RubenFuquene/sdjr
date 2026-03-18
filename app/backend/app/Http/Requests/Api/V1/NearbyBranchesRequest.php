<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

class NearbyBranchesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Endpoint público
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:'.Constant::MAX_SEARCH_RADIUS_KM],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function validatedRadius(): float
    {
        return (float) ($this->input('radius', Constant::DEFAULT_SEARCH_RADIUS_KM));
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', Constant::DEFAULT_PER_PAGE));
    }
}
