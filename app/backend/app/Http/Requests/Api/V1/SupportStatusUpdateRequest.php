<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="SupportStatusUpdateRequest",
 *     required={"name", "code", "color"},
 *     @OA\Property(property="name", type="string", maxLength=100, example="Abierto", description="Support status name"),
 *     @OA\Property(property="code", type="string", maxLength=20, example="OPEN", description="Support status code (unique)"),
 *     @OA\Property(property="color", type="string", maxLength=20, example="green", description="Color name or hex"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)")
 * )
 */
class SupportStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.support_statuses.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:support_statuses,code,' . $this->route('support_status')],
            'color' => ['required', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:1'],
        ];
    }
}
