<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowEstablishmentTypeRequest",
 *     description="Request for showing an establishment type",
 *
 *     @OA\Property(property="id", type="integer", description="Establishment Type ID")
 * )
 */
class ShowEstablishmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para ver un tipo de establecimiento
        return $this->user()?->can('provider.establishment_types.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
