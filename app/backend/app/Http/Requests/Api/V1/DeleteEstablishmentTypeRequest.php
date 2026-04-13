<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="DeleteEstablishmentTypeRequest",
 *     description="Request for deleting an establishment type",
 *
 *     @OA\Property(property="id", type="integer", description="Establishment Type ID")
 * )
 */
class DeleteEstablishmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para eliminar un tipo de establecimiento
        return $this->user()?->can('provider.establishment_types.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
