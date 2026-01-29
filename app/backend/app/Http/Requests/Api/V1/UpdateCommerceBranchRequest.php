<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UpdateCommerceBranchRequest",
 *   type="object",
 *
 *   @OA\Property(property="department_id", type="integer", example=1),
 *   @OA\Property(property="city_id", type="integer", example=1),
 *   @OA\Property(property="neighborhood_id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Sucursal Editada"),
 *   @OA\Property(property="address", type="string", example="Calle 456 #78-90"),
 *   @OA\Property(property="latitude", type="number", format="float", example=4.7),
 *   @OA\Property(property="longitude", type="number", format="float", example=-74.2),
 *   @OA\Property(property="phone", type="string", example="3009876543"),
 *   @OA\Property(property="email", type="string", example="editada@comercio.com"),
 *   @OA\Property(property="status", type="boolean", example=false)
 * )
 */
class UpdateCommerceBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.commerces.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'neighborhood_id' => ['sometimes', 'integer', 'exists:neighborhoods,id'],
            'name' => ['sometimes', 'string', 'max:100'],
            'address' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'status' => ['boolean'],
        ];
    }
}
