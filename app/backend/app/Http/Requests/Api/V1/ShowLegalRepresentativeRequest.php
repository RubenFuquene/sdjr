<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\LegalRepresentative;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowLegalRepresentativeRequest",
 *     description="Request for showing a legal representative",
 *
 *     @OA\Property(property="id", type="integer", description="Legal Representative ID")
 * )
 */
class ShowLegalRepresentativeRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        if (! $this->user()?->can('provider.legal_representatives.show')) {
            return false;
        }

        $legalRepresentativeId = (int) ($this->route('legal_representative') ?? 0);
        if ($legalRepresentativeId <= 0) {
            return false;
        }

        $commerceId = LegalRepresentative::query()->whereKey($legalRepresentativeId)->value('commerce_id');

        return $commerceId !== null && $this->userCanAccessCommerce((int) $commerceId);
    }

    public function rules(): array
    {
        return [];
    }
}
