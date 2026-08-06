<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-334: dedicado a GET /commerces/{commerce_id}/branches. No se reutiliza
 * IndexCommerceBranchRequest (usado también por el índice general
 * GET /commerce-branches, sin commerce_id de ruta) para no acoplar el
 * ownership de este endpoint al de uno distinto — ver regla de owasp.md sobre
 * grep global de consumidores antes de tocar un FormRequest compartido.
 */
class IndexBranchesByCommerceRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        if (! $this->user()?->can('provider.branches.show')) {
            return false;
        }

        return $this->userCanAccessCommerce((int) $this->route('commerce_id'));
    }

    public function rules(): array
    {
        return [];
    }
}
