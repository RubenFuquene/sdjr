<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="EstablishmentTypeRequest",
 *     required={"name","code"},
 *
 *     @OA\Property(property="name", type="string", maxLength=255, example="Restaurante"),
 *     @OA\Property(property="code", type="string", maxLength=20, example="REST")
 * )
 */
class EstablishmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'provider.establishment_types.'.($action === 'store' ? 'create' : 'update');

        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:establishment_types,code'],
        ];

        return $rules;
    }
}
