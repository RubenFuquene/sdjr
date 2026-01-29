<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DeleteDepartmentRequest
 *
 * @OA\Schema(
 *     schema="DeleteDepartmentRequest",
 *     type="object",
 *     description="Request schema for deleting a department. No body parameters required."
 * )
 */
class DeleteDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.departments.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
