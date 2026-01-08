<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DeleteNeighborhoodRequest
 *
 *
 * @OA\Schema(
 *     schema="DeleteNeighborhoodRequest",
 *     required={"id"},
 *
 *     @OA\Property(property="id", type="integer", example=1, description="Neighborhood ID")
 * )
 */
class DeleteNeighborhoodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.neighborhoods.delete') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
