<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DeletePqrsTypeRequest
 *
 * @OA\Schema(
 *     schema="DeletePqrsTypeRequest",
 *     type="object",
 *     description="Request schema for deleting a PQRS type. No body parameters required."
 * )
 */
class DeletePqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.pqrs_types.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
