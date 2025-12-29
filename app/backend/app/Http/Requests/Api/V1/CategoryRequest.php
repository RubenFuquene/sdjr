<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      title="Category Request",
 *      description="Category request body data",
 *      type="object",
 *      required={"name"}
 * )
 */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'admin.categories.'.($action === 'store' ? 'create' : 'update');

        return $this->user()?->can($permission) ?? false;
    }

    /**
     * @OA\Property(property="name", type="string", example="Food")
     * @OA\Property(property="icon", type="string", example="https://example.com/icon.png")
     * @OA\Property(property="status", type="string", example="1")
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'status' => 'nullable|in:0,1',
        ];
    }
}
