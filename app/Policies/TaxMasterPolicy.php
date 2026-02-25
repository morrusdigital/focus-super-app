<?php

namespace App\Policies;

use App\Models\TaxMaster;
use App\Models\User;

class TaxMasterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin();
    }

    public function view(User $user, TaxMaster $taxMaster): bool
    {
        return $user->isCompanyAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isCompanyAdmin();
    }

    public function update(User $user, TaxMaster $taxMaster): bool
    {
        return $user->isCompanyAdmin();
    }

    public function delete(User $user, TaxMaster $taxMaster): bool
    {
        return $user->isCompanyAdmin();
    }
}
