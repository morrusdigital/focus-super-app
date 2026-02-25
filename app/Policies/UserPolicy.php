<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // ---------------------------------------------------------------
    // Private helper
    // ---------------------------------------------------------------

    /**
     * Actor and target belong to the same company.
     */
    private function isSameCompany(User $actor, User $target): bool
    {
        return (int) $actor->company_id === (int) $target->company_id;
    }

    // ---------------------------------------------------------------
    // Abilities
    // ---------------------------------------------------------------

    /**
     * viewAny — can reach the user list page.
     *   holding_admin → yes (all companies)
     *   company_admin → yes (own company only, filtered at query level)
     *   others        → no
     */
    public function viewAny(User $actor): bool
    {
        return $actor->isHoldingAdmin() || $actor->isCompanyAdmin();
    }

    /**
     * view — can see a specific user's profile.
     *   holding_admin → any user
     *   company_admin → same company only
     *   others        → no
     */
    public function view(User $actor, User $target): bool
    {
        if ($actor->isHoldingAdmin()) {
            return true;
        }

        if ($actor->isCompanyAdmin()) {
            return $this->isSameCompany($actor, $target);
        }

        return false;
    }

    /**
     * create — can open create form and create a new user.
     *   holding_admin → yes (for any company)
     *   company_admin → yes (own company only, enforced in controller)
     *   others        → no
     */
    public function create(User $actor): bool
    {
        return $actor->isHoldingAdmin() || $actor->isCompanyAdmin();
    }

    /**
     * update — can edit a user's data.
     *   holding_admin → any user
     *   company_admin → same company only
     *   others        → no
     */
    public function update(User $actor, User $target): bool
    {
        if ($actor->isHoldingAdmin()) {
            return true;
        }

        if ($actor->isCompanyAdmin()) {
            return $this->isSameCompany($actor, $target);
        }

        return false;
    }

    /**
     * activate — can toggle a user's is_active flag.
     *   Same rules as update.
     */
    public function activate(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    /**
     * resetPassword — can reset another user's password.
     *   Same rules as update.
     */
    public function resetPassword(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }
}
