<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    /**
     * User belongs to the same company as the project.
     */
    private function isSameCompany(User $user, Project $project): bool
    {
        return (int) $project->company_id === (int) $user->company_id;
    }

    /**
     * User is the assigned project manager of this project.
     */
    private function isProjectManager(User $user, Project $project): bool
    {
        return $project->project_manager_id !== null
            && (int) $project->project_manager_id === (int) $user->id;
    }

    /**
     * User is a member of this project (via project_members pivot).
     */
    private function isMemberOf(User $user, Project $project): bool
    {
        return $project->members()->where('users.id', $user->id)->exists();
    }

    // ---------------------------------------------------------------
    // MVP access matrix
    // ---------------------------------------------------------------

    /**
     * viewAny — all users with a valid final role can reach the project list;
     * filtering to their visible subset is done at the query level.
     */
    public function viewAny(User $user): bool
    {
        return $user->isHoldingAdmin()
            || $user->isCompanyAdmin()
            || $user->isFinanceHolding()
            || $user->isFinanceCompany()
            || $user->isEmployee();
    }

    /**
     * view — who can see a specific project:
     *   holding_admin / finance_holding → any project
     *   company_admin / finance_company → own company only
     *   employee → projects they manage (project_manager_id) or are a member of
     */
    public function view(User $user, Project $project): bool
    {
        if ($user->isHoldingAdmin() || $user->isFinanceHolding()) {
            return true;
        }

        if ($user->isCompanyAdmin() || $user->isFinanceCompany()) {
            return $this->isSameCompany($user, $project);
        }

        if ($user->isEmployee()) {
            return $this->isProjectManager($user, $project)
                || $this->isMemberOf($user, $project);
        }

        return false;
    }

    /**
     * create — holding_admin and company_admin may create projects.
     */
    public function create(User $user): bool
    {
        return $user->isHoldingAdmin() || $user->isCompanyAdmin();
    }

    /**
     * update — edit the project record itself.
     *   holding_admin → any project
     *   company_admin → own company only
     *   project_manager / member → NOT allowed (they manage tasks, not the project)
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $project);
        }

        return false;
    }

    /**
     * manageTasks — create / edit tasks inside the project.
     *   PM (project_manager_id) → can manage tasks
     *   all other roles → NOT allowed
     */
    public function manageTasks(User $user, Project $project): bool
    {
        return $this->isProjectManager($user, $project);
    }

    /**
     * viewKanban — view the kanban board and task list.
     *   PM (project_manager_id) or project member → allowed
     *   all admin roles → NOT allowed
     */
    public function viewKanban(User $user, Project $project): bool
    {
        return $this->isProjectManager($user, $project)
            || $this->isMemberOf($user, $project);
    }

    /**
     * manageMembers — add/remove project members.
     *   holding_admin  → any project
     *   company_admin  → own company only
     *   PM (project_manager_id) → only the project they manage
     */
    public function manageMembers(User $user, Project $project): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $project);
        }

        return $this->isProjectManager($user, $project);
    }

    // ---------------------------------------------------------------
    // Existing abilities — preserved for backward compatibility
    // ---------------------------------------------------------------

    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    public function manageTerms(User $user, Project $project): bool
    {
        return $user->isCompanyAdmin() && $this->isSameCompany($user, $project);
    }

    public function manageReceipts(User $user, Project $project): bool
    {
        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $project);
        }

        if ($user->isFinanceHolding()) {
            return (int) ($project->company?->parent_id ?? 0) === (int) $user->company_id;
        }

        return false;
    }

    public function manageVendors(User $user, Project $project): bool
    {
        return $user->isCompanyAdmin() && $this->isSameCompany($user, $project);
    }

    public function manageExpenses(User $user, Project $project): bool
    {
        return $user->isCompanyAdmin() && $this->isSameCompany($user, $project);
    }

    public function manageProgress(User $user, Project $project): bool
    {
        return $user->isCompanyAdmin() && $this->isSameCompany($user, $project);
    }

    public function approvePartialReceipt(User $user, Project $project): bool
    {
        return $user->isFinanceHolding()
            && (int) ($project->company?->parent_id ?? 0) === (int) $user->company_id;
    }
}
