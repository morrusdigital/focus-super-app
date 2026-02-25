<?php

namespace App\Policies;

use App\Models\ChartAccount;
use App\Models\User;

class ChartAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin();
    }

    public function view(User $user, ChartAccount $account): bool
    {
        return $user->isCompanyAdmin() && $account->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isCompanyAdmin();
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
