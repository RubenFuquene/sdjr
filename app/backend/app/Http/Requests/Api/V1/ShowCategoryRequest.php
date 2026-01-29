<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShowCategoryRequest
 *
 * @OA\Schema(
 *     schema="ShowCategoryRequest",
 *     type="object",
 *     description="Request schema for showing a category. No body parameters required."
 * )
 */
class ShowCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.categories.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
