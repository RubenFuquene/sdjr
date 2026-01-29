<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DeleteCategoryRequest
 *
 * @OA\Schema(
 *     schema="DeleteCategoryRequest",
 *     type="object",
 *     description="Request schema for deleting a category. No body parameters required."
 * )
 */
class DeleteCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.categories.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
