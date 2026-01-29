<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShowDepartmentRequest
 *
 * @OA\Schema(
 *     schema="ShowDepartmentRequest",
 *     type="object",
 *     description="Request schema for showing a department. No body parameters required."
 * )
 */
class ShowDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.departments.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
