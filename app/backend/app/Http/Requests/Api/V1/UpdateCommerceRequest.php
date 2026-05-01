<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Commerce;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateCommerceRequest",
 *     required={"owner_user_id","department_id","city_id","neighborhood_id","name","tax_id","tax_id_type","address"},
 *
 *     @OA\Property(property="owner_user_id", type="integer", example=1),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="city_id", type="integer", example=1),
 *     @OA\Property(property="neighborhood_id", type="integer", example=1),
 *     @OA\Property(property="establishment_type_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", maxLength=255, example="Comercial S.A.S"),
 *     @OA\Property(property="description", type="string", maxLength=500, example="Comercio de tecnologia"),
 *     @OA\Property(property="tax_id", type="string", maxLength=30, example="900123456"),
 *     @OA\Property(property="tax_id_type", type="string", maxLength=10, example="NIT"),
 *     @OA\Property(property="address", type="string", maxLength=255, example="Calle 123 #45-67"),
 *     @OA\Property(property="phone", type="string", maxLength=20, example="3001234567"),
 *     @OA\Property(property="email", type="string", maxLength=100, example="info@comercial.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="is_verified", type="integer", enum={0,1,2,3}, example=0)
 * )
 */
class UpdateCommerceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $canUpdateAsProvider = $user->can('provider.commerces.update');
        if (! $canUpdateAsProvider) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $commerceId = (int) ($this->route('commerce_id') ?? $this->route('id') ?? $this->route('commerce') ?? 0);
        if ($commerceId <= 0) {
            return false;
        }

        return Commerce::query()
            ->whereKey($commerceId)
            ->where('owner_user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'owner_user_id' => ['required', 'integer', 'exists:users,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'neighborhood_id' => ['required', 'integer', 'exists:neighborhoods,id'],
            'establishment_type_id' => ['nullable', 'integer', 'exists:establishment_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'tax_id' => ['required', 'string', 'max:30'],
            'tax_id_type' => ['required', 'string', 'max:10'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'max:100', 'email'],
            'is_active' => ['boolean'],
            'is_verified' => ['integer', 'in:0,1,2,3'],
            'terms_accepted_version' => ['nullable', 'integer', 'min:1'],
            'terms_accepted_at' => ['nullable', 'date'],
        ];
    }
}
