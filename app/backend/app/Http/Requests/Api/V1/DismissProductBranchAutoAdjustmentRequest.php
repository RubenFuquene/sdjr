<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Commerce;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-361, Tarea 3.8: descarta el aviso de ajuste automático de un pack
 * en una sede. Mismo criterio de autorización que
 * UpdateProductBranchPublicationRequest (ownership por comercio) — no
 * cambia inventario ni publicación, así que no reutiliza validaciones de
 * negocio de esos otros FormRequest.
 */
class DismissProductBranchAutoAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->can('provider.products.update')) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $product = Product::find($this->route('id'));

        if (! $product) {
            return false;
        }

        return Commerce::query()
            ->where('id', $product->commerce_id)
            ->where('owner_user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [];
    }
}
