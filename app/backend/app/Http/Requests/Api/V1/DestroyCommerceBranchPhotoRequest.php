<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\CommerceBranch;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-316 (ampliado) — antes DELETE /commerce-branches/photos/{photo} compartía
 * DestroyDocumentUploadRequest (permiso provider.products.delete, sin ownership real).
 * Ahora exige provider.photos.delete y ownership del comercio dueño de la sucursal.
 *
 * Nota: el controller resuelve la foto vía CommerceBranch::class (no CommerceBranchPhoto,
 * que sí existe como modelo/tabla dedicados) — bug funcional documentado en SCRUM-273, fuera
 * de alcance de esta auditoría de autorización. Este Request resuelve ownership de forma
 * deliberadamente coherente con el modelo que el controller usa hoy.
 */
class DestroyCommerceBranchPhotoRequest extends FormRequest
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

        $branch = CommerceBranch::find($this->route('photo'));

        if (! $branch) {
            // Sin registro: el controller responde 404 (ModelNotFoundException), no 403.
            return true;
        }

        return $this->userCanAccessCommerce((int) $branch->commerce_id);
    }

    public function rules(): array
    {
        return [];
    }
}
