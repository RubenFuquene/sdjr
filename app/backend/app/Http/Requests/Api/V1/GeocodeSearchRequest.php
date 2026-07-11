<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="GeocodeSearchRequest",
 *
 *     @OA\Property(property="q", type="string", maxLength=255, example="Calle 10 #5-20, La Candelaria, Bogotá", description="Dirección de texto a geocodificar")
 * )
 */
class GeocodeSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.geocode.search') ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    public function validatedQuery(): string
    {
        return (string) $this->validated()['q'];
    }
}
