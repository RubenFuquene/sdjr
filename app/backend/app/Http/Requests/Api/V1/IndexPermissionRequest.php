<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="IndexPermissionRequest",
 *     description="Request for listing permissions",
 *     @OA\Property(property="name", type="string", description="Filter by permission name"),
 *     @OA\Property(property="guard_name", type="string", description="Filter by guard name")
 * )
 */
class IndexPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso para listar permisos (ajustar si existe uno más específico)
        return $this->user()?->can('admin.profiles.permissions.index') ?? false;
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
