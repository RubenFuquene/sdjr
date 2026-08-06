<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

class PatchCommerceStatusRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    /**
     * Autoriza la petición solo si el usuario tiene el permiso provider.commerces.update
     * y el comercio le pertenece.
     */
    public function authorize(): bool
    {
        if (! $this->user()?->can('provider.commerces.update')) {
            return false;
        }

        $commerceId = (int) ($this->route('commerce_id') ?? $this->route('id') ?? $this->route('commerce') ?? 0);

        return $this->userCanAccessCommerce($commerceId);
    }

    /**
     * Reglas de validación para el estado del comercio.
     */
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'integer', 'in:'.Constant::STATUS_ACTIVE.','.Constant::STATUS_INACTIVE],
        ];
    }
}
