<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

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
    public function authorize(): bool
    {
        // Permiso para ver un representante legal
        return $this->user()?->can('provider.legal_representatives.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
