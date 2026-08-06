<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowCommerceRequest",
 *     description="Request for showing a commerce",
 *
 *     @OA\Property(property="id", type="integer", description="Commerce ID")
 * )
 */
class ShowCommerceRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        if (! $this->user()?->can('provider.commerces.show')) {
            return false;
        }

        $commerceId = (int) ($this->route('commerce_id') ?? $this->route('id') ?? $this->route('commerce') ?? 0);

        return $this->userCanAccessCommerce($commerceId);
    }

    public function rules(): array
    {
        return [];
    }
}
