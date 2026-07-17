<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\CommerceDocument;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-315 — cierra el IDOR de download-url: exige ownership del comercio dueño
 * del documento (o permiso admin) antes de generar la URL firmada de descarga.
 */
class ShowDocumentDownloadUrlRequest extends FormRequest
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

        if (! $user->can('provider.documents.view')) {
            return false;
        }

        $document = CommerceDocument::find($this->route('id'));

        if (! $document) {
            // Sin documento: el controller responde 404 (ModelNotFoundException), no 403.
            return true;
        }

        return $this->userCanAccessCommerce((int) $document->commerce_id);
    }

    public function rules(): array
    {
        return [];
    }
}
