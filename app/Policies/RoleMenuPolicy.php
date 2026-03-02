<?php

namespace App\Policies;

use App\Models\User;

/**
 * Only holding_admin may manage the role-menu configuration.
 * All other roles are denied every action.
 */
class RoleMenuPolicy
{
    public function before(User $user): ?bool
    {
        // holding_admin bypasses all checks → always allowed
        if ($user->isHoldingAdmin()) {
            return true;
        }

        // Everyone else is denied
        return false;
    }

    public function viewAny(User $user): bool
    {
        return false; // never reached; handled by before()
    }

    public function update(User $user): bool
    {
        return false; // never reached; handled by before()
    }
}
