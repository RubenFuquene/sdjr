<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShowPqrsTypeRequest
 *
 * @OA\Schema(
 *     schema="ShowPqrsTypeRequest",
 *     type="object",
 *     description="Request schema for showing a PQRS type. No body parameters required."
 * )
 */
class ShowPqrsTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.pqrs_types.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
