<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-343: `GET /products/commerce/{commerce_id}` estaba bajo auth:sanctum
 * sin ningún FormRequest — cualquier autenticado veía el catálogo de
 * cualquier comercio. Se decidió privado: permiso + propiedad, ya que el
 * único consumidor real (panel de proveedor) siempre consulta su propio
 * comercio, y el browsing público ya tiene nearby/* y catalog/*.
 */
class ProductsByCommerceRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        if (! $this->user()?->can('provider.products.index')) {
            return false;
        }

        return $this->userCanAccessCommerce((int) $this->route('commerce_id'));
    }

    public function rules(): array
    {
        return [];
    }
}
