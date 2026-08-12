<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-362: GET /commerces/{commerce_id}/fiscal-codes. Dedicado en vez de
 * reutilizar otro FormRequest de comercio, para no acoplar el ownership de
 * este endpoint al de uno distinto (ver owasp.md sobre grep de consumidores
 * antes de tocar un FormRequest compartido).
 */
class ShowCommerceFiscalCodesRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if (! ($user->can('provider.products.create') || $user->can('provider.products.update'))) {
            return false;
        }

        return $this->userCanAccessCommerce((int) $this->route('commerce_id'));
    }

    public function rules(): array
    {
        return [];
    }
}
