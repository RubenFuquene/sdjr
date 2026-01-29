<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShowPriorityTypeRequest
 *
 * @OA\Schema(
 *     schema="ShowPriorityTypeRequest",
 *     type="object",
 *     description="Request schema for showing a priority type. No body parameters required."
 * )
 */
class ShowPriorityTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.priority_types.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
