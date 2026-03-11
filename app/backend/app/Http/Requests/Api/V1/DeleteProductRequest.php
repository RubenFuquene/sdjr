<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="DeleteProductRequest",
 *   required={"id"},
 *
 *   @OA\Property(property="id", type="integer", example=1, description="ID of the product to delete")
 * )
 */
class DeleteProductRequest extends FormRequest
{
    /**
     * Authorize the request based on user permissions.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('provider.products.delete') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
