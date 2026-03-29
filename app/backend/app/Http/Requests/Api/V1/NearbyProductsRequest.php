<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="NearbyProductsRequest",
 *   type="object",
 *   required={"latitude","longitude"},
 *
 *   @OA\Property(property="latitude", type="number", format="float", example=19.4326, minimum=-90, maximum=90),
 *   @OA\Property(property="longitude", type="number", format="float", example=-99.1332, minimum=-180, maximum=180),
 *   @OA\Property(property="radius", type="number", format="float", example=10, minimum=0.1),
 *   @OA\Property(property="category_id", type="integer", example=5),
 *   @OA\Property(property="max_price", type="number", format="float", example=100),
 *   @OA\Property(property="per_page", type="integer", example=15, minimum=1, maximum=50)
 * )
 */
class NearbyProductsRequest extends FormRequest
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
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
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

    public function filters(): array
    {
        return $this->only(['category_id', 'max_price']);
    }
}
