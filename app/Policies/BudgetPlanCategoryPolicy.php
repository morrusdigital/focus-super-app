<?php

namespace App\Policies;

use App\Models\BudgetPlanCategory;
use App\Models\User;

class BudgetPlanCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCompanyAdmin();
    }

    public function view(User $user, BudgetPlanCategory $category): bool
    {
        return $user->isCompanyAdmin() && $category->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isCompanyAdmin();
    }

    public function update(User $user, BudgetPlanCategory $category): bool
    {
        return $this->view($user, $category);
    }

    public function delete(User $user, BudgetPlanCategory $category): bool
    {
        return $this->update($user, $category);
    }
}
