<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\ProductPhoto;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-316 (ampliado) — antes DELETE /products/commerce/photos/{photo} compartía
 * DestroyDocumentUploadRequest (permiso provider.products.delete, sin ownership real).
 * Ahora exige provider.photos.delete y ownership del comercio dueño del producto.
 */
class DestroyProductPhotoRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->can('admin.providers.documents.manage')) {
            return true;
        }

        if (! $user->can('provider.photos.delete')) {
            return false;
        }

        $photo = ProductPhoto::with('product')->find($this->route('photo'));

        if (! $photo) {
            // Sin foto: el controller responde 404 (ModelNotFoundException), no 403.
            return true;
        }

        return $this->userCanAccessCommerce((int) $photo->product?->commerce_id);
    }

    public function rules(): array
    {
        return [];
    }
}
