<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DeletePriorityTypeRequest
 *
 * @OA\Schema(
 *     schema="DeletePriorityTypeRequest",
 *     type="object",
 *     description="Request schema for deleting a priority type. No body parameters required."
 * )
 */
class DeletePriorityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.priority_types.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
