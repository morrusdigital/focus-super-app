<?php

namespace App\Policies;

use App\Models\ChartAccount;
use App\Models\User;

class ChartAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function view(User $user, ChartAccount $account): bool
    {
        return $user->isAdminCompany() && $account->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function update(User $user, ChartAccount $account): bool
    {
        return $this->view($user, $account);
    }

    public function delete(User $user, ChartAccount $account): bool
    {
        return $this->update($user, $account);
    }
}
