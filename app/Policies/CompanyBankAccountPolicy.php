<?php

namespace App\Policies;

use App\Models\CompanyBankAccount;
use App\Models\User;

class CompanyBankAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function view(User $user, CompanyBankAccount $bankAccount): bool
    {
        return $user->isAdminCompany() && $bankAccount->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function update(User $user, CompanyBankAccount $bankAccount): bool
    {
        return $user->isAdminCompany() && $bankAccount->company_id === $user->company_id;
    }

    public function delete(User $user, CompanyBankAccount $bankAccount): bool
    {
        return $this->update($user, $bankAccount);
    }
}
