<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\CommerceBranch;
use App\Services\CommerceBranchUserService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowCommerceBranchUsersRequest",
 *     type="object",
 *     required={"commerce_branch_id"},
 *       description="Items per page for pagination (defaults to 15)"
 *     )
 * )
 */
class ShowCommerceBranchUsersRequest extends FormRequest
{
    /**
     * SCRUM-334: antes solo exigía el rol — cualquier provider o branch_leader
     * veía los usuarios de CUALQUIER sucursal. Dos caminos válidos de
     * propiedad: el dueño del comercio de la sucursal, o un branch_leader
     * asignado específicamente a esa sucursal (no a cualquiera).
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        if (! $user->hasRole(['provider', 'branch_leader'])) {
            return false;
        }

        $commerceBranchId = (int) ($this->route('commerce_branch_id') ?? 0);
        if ($commerceBranchId <= 0) {
            return false;
        }

        $ownsCommerce = CommerceBranch::query()
            ->whereKey($commerceBranchId)
            ->whereHas('commerce', function ($query) use ($user): void {
                $query->where('owner_user_id', $user->id);
            })
            ->exists();

        if ($ownsCommerce) {
            return true;
        }

        return app(CommerceBranchUserService::class)->isUserAssignedToBranch($user->id, $commerceBranchId);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get the validated per_page value with default.
     */
    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }
}
