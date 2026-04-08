<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="IndexRoleRequest",
 *     description="Request for listing roles",
 *
 *     @OA\Property(property="name", type="string", description="Filter by role name"),
 *     @OA\Property(property="guard_name", type="string", description="Filter by guard name")
 * )
 */
class IndexRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para listar roles
        return $this->user()?->can('admin.profiles.roles.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'guard_name' => ['sometimes', 'string'],
        ];
    }

    /**
     * Devuelve los filtros validados para el index.
     */
    public function validatedFilters(): array
    {
        return $this->only(['name', 'guard_name']);
    }
}
