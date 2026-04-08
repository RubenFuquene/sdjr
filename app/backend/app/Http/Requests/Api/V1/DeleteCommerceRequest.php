<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="DeleteCommerceRequest",
 *     description="Request for deleting a commerce",
 *
 *     @OA\Property(property="id", type="integer", description="Commerce ID")
 * )
 */
class DeleteCommerceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para eliminar un comercio
        return $this->user()?->can('provider.commerces.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
