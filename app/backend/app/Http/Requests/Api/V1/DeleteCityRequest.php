<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DeleteCityRequest
 *
 * @OA\Schema(
 *     schema="DeleteCityRequest",
 *     type="object",
 *     description="Request schema for deleting a city. No body parameters required."
 * )
 */
class DeleteCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.cities.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
