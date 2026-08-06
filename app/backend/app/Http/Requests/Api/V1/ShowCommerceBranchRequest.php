<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\CommerceBranch;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

class ShowCommerceBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if (! $user->can('provider.branches.show')) {
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
        return [];
    }
}
