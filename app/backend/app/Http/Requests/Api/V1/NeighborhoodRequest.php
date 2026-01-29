<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * NeighborhoodRequest
 *
 * @OA\Schema(
 *     schema="NeighborhoodRequest",
 *     type="object",
 *     required={"city_id", "name", "code"},
 *
 *     @OA\Property(property="city_id", type="integer", example=1, description="ID of the city"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="Chapinero", description="Name of the neighborhood"),
 *     @OA\Property(property="code", type="string", maxLength=6, example="NB0001", description="Unique code for the neighborhood (6 alphanumeric characters)"),
 *     @OA\Property(property="status", type="string", example="A", description="Status of the neighborhood (A: Active, I: Inactive)")
 * )
 */
class NeighborhoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'admin.params.neighborhoods.'.($action === 'store' ? 'create' : 'update');

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        return [
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:6|regex:/^[A-Za-z0-9]+$/|unique:neighborhoods,code',
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
