<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

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
    public function authorize(): bool
    {
        // Permiso para ver un comercio
        return $this->user()?->can('provider.commerces.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
