<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="GeocodeReverseRequest",
 *
 *     @OA\Property(property="lat", type="number", format="float", minimum=-90, maximum=90, example=4.598),
 *     @OA\Property(property="lng", type="number", format="float", minimum=-180, maximum=180, example=-74.076)
 * )
 */
class GeocodeReverseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.geocode.reverse') ?? false;
    }

    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function validatedLat(): float
    {
        return (float) $this->validated()['lat'];
    }

    public function validatedLng(): float
    {
        return (float) $this->validated()['lng'];
    }
}
