<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminCompany() || $user->isFinanceHolding();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->isAdminCompany()) {
            return $project->company_id === $user->company_id;
        }

        if ($user->isFinanceHolding()) {
            return (int) ($project->company?->parent_id ?? 0) === (int) $user->company_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminCompany();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdminCompany() && $project->company_id === $user->company_id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    public function manageTerms(User $user, Project $project): bool
    {
        return $user->isAdminCompany() && $project->company_id === $user->company_id;
    }

    public function manageReceipts(User $user, Project $project): bool
    {
        if ($user->isAdminCompany()) {
            return $project->company_id === $user->company_id;
        }

        if ($user->isFinanceHolding()) {
            return (int) ($project->company?->parent_id ?? 0) === (int) $user->company_id;
        }

        return false;
    }

    public function manageVendors(User $user, Project $project): bool
    {
        return $user->isAdminCompany() && $project->company_id === $user->company_id;
    }

    public function manageExpenses(User $user, Project $project): bool
    {
        return $user->isAdminCompany() && $project->company_id === $user->company_id;
    }

    public function approvePartialReceipt(User $user, Project $project): bool
    {
        return $user->isFinanceHolding() && (int) ($project->company?->parent_id ?? 0) === (int) $user->company_id;
    }
}
