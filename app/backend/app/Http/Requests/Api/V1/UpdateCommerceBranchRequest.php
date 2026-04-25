<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\CommerceBranch;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UpdateCommerceBranchRequest",
 *   type="object",
 *
 *   @OA\Property(property="department_id", type="integer", example=1),
 *   @OA\Property(property="city_id", type="integer", example=1),
 *   @OA\Property(property="neighborhood_id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Sucursal Editada"),
 *   @OA\Property(property="address", type="string", example="Calle 456 #78-90"),
 *   @OA\Property(property="latitude", type="number", format="float", example=4.7),
 *   @OA\Property(property="longitude", type="number", format="float", example=-74.2),
 *   @OA\Property(property="phone", type="string", example="3009876543"),
 *   @OA\Property(property="email", type="string", example="editada@comercio.com"),
 *   @OA\Property(property="status", type="boolean", example=false)
 * )
 */
class UpdateCommerceBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $canUpdateAsProvider = $user->can('provider.branches.update');
        if (! $canUpdateAsProvider) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $branchId = (int) ($this->route('id') ?? $this->route('commerce_branch') ?? 0);
        if ($branchId <= 0) {
            return false;
        }

        return CommerceBranch::query()
            ->whereKey($branchId)
            ->where(function (Builder $query) use ($user): void {
                $query->whereHas('commerce', function (Builder $commerceQuery) use ($user): void {
                    $commerceQuery->where('owner_user_id', $user->id);
                });
            })
            ->exists();

    }

    public function rules(): array
    {
        return [
            'commerce_branch.commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'commerce_branch.department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'commerce_branch.city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'commerce_branch.neighborhood_id' => ['sometimes', 'integer', 'exists:neighborhoods,id'],
            'commerce_branch.name' => ['required', 'string', 'max:100'],
            'commerce_branch.address' => ['sometimes', 'string', 'max:255'],
            'commerce_branch.latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'commerce_branch.longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'commerce_branch.phone' => ['sometimes', 'string', 'max:20'],
            'commerce_branch.email' => ['sometimes', 'email', 'max:100'],
            'commerce_branch.status' => ['sometimes', 'boolean'],

            'commerce_branch_hours' => ['sometimes', 'array', 'min:1'],
            'commerce_branch_hours.*.day_of_week' => ['sometimes', 'integer', 'between:0,6', 'distinct'],
            'commerce_branch_hours.*.open_time' => ['sometimes', 'date_format:H:i'],
            'commerce_branch_hours.*.close_time' => ['sometimes', 'date_format:H:i'],
            'commerce_branch_hours.*.note' => ['sometimes'],

            'commerce_branch_photos' => ['sometimes', 'array', 'max:'.Constant::MAX_PHOTOS_PER_COMMERCE_BRANCH],
            'commerce_branch_photos.*.file_name' => ['sometimes', 'string', 'max:255'],
            'commerce_branch_photos.*.mime_type' => ['sometimes', 'string', 'in:'.implode(',', Constant::ALLOWED_PHOTO_EXTENSIONS)],
            'commerce_branch_photos.*.file_size_bytes' => ['sometimes', 'integer', 'min:1', 'max:'.Constant::ALLOWED_PHOTO_SIZE_BYTES],
            'commerce_branch_photos.*.versioning_enabled' => ['sometimes', 'string'],
            'commerce_branch_photos.*.metadata' => ['sometimes', 'array'],
        ];
    }
}
