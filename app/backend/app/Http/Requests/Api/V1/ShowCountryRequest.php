<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowCountryRequest",
 *     required={"id"},
 *
 *     @OA\Property(property="id", type="string", description="Country ID")
 * )
 */
class ShowCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.countries.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
