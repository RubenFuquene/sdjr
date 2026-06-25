<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Commerce;

/**
 * Resolves whether the authenticated user may operate over a given commerce.
 *
 * Intended for FormRequests whose route contains a commerce identifier.
 * Admins/superadmins always pass; any other role must own the commerce.
 */
trait AuthorizesCommerceOwnership
{
    /**
     * Resolve the commerce id from the common route parameter names.
     */
    protected function resolveCommerceId(): int
    {
        return (int) ($this->route('commerce_id') ?? $this->route('commerce') ?? $this->route('id') ?? 0);
    }

    /**
     * Whether the authenticated user may access the given commerce.
     * Falls back to the commerce id resolved from the route when none is provided.
     */
    protected function userCanAccessCommerce(?int $commerceId = null): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $commerceId ??= $this->resolveCommerceId();

        if ($commerceId <= 0) {
            return false;
        }

        return Commerce::query()
            ->whereKey($commerceId)
            ->where('owner_user_id', $user->id)
            ->exists();
    }
}
