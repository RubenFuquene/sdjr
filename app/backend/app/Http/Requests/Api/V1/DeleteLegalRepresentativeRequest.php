<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="DeleteLegalRepresentativeRequest",
 *     description="Request for deleting a legal representative",
 *
 *     @OA\Property(property="id", type="integer", description="Legal Representative ID")
 * )
 */
class DeleteLegalRepresentativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para eliminar un representante legal
        return $this->user()?->can('provider.legal_representatives.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
