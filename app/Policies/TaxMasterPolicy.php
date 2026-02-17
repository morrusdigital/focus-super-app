<?php

namespace App\Policies;

use App\Models\TaxMaster;
use App\Models\User;

class TaxMasterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function view(User $user, TaxMaster $taxMaster): bool
    {
        return $user->isAdminCompany();
    }

    public function create(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function update(User $user, TaxMaster $taxMaster): bool
    {
        return $user->isAdminCompany();
    }

    public function delete(User $user, TaxMaster $taxMaster): bool
    {
        return $user->isAdminCompany();
    }
}
