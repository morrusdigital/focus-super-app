<?php

namespace App\Policies;

use App\Models\TaskProject;
use App\Models\User;

/**
 * Policy for the Task Project module (Issue #35).
 *
 * Access matrix:
 *  holding_admin   → full access, all companies (lintas company)
 *  company_admin   → full access, same company only
 *  employee        → view if PM/creator/task-assignee; manage if PM/creator; create always
 *  finance_holding → read-only, all companies
 *  finance_company → read-only, same company only
 */
class TaskProjectPolicy
{
    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function isSameCompany(User $user, TaskProject $tp): bool
    {
        return (int) $tp->company_id === (int) $user->company_id;
    }

    private function isProjectManager(User $user, TaskProject $tp): bool
    {
        return $tp->project_manager_id !== null
            && (int) $tp->project_manager_id === (int) $user->id;
    }

    private function isCreator(User $user, TaskProject $tp): bool
    {
        return (int) $tp->created_by === (int) $user->id;
    }

    /**
     * Employee qualifies as "involved" if they're the PM, creator,
     * or assigned to at least one task inside this project.
     */
    private function isInvolved(User $user, TaskProject $tp): bool
    {
        if ($this->isProjectManager($user, $tp) || $this->isCreator($user, $tp)) {
            return true;
        }

        // Check task assignees
        return $tp->tasks()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->exists();
    }

    // ---------------------------------------------------------------
    // Abilities
    // ---------------------------------------------------------------

    /** All five roles can reach the list page (scope applied in controller). */
    public function viewAny(User $user): bool
    {
        return $user->isHoldingAdmin()
            || $user->isCompanyAdmin()
            || $user->isFinanceHolding()
            || $user->isFinanceCompany()
            || $user->isEmployee();
    }

    /**
     * holding_admin / finance_holding → any project.
     * company_admin / finance_company → same company.
     * employee                        → same company + involved.
     */
    public function view(User $user, TaskProject $tp): bool
    {
        if ($user->isHoldingAdmin() || $user->isFinanceHolding()) {
            return true;
        }

        if ($user->isCompanyAdmin() || $user->isFinanceCompany()) {
            return $this->isSameCompany($user, $tp);
        }

        if ($user->isEmployee()) {
            return $this->isSameCompany($user, $tp)
                && $this->isInvolved($user, $tp);
        }

        return false;
    }

    /** holding_admin, company_admin, employee can create. Finance cannot. */
    public function create(User $user): bool
    {
        return $user->isHoldingAdmin()
            || $user->isCompanyAdmin()
            || $user->isEmployee();
    }

    /**
     * holding_admin → any.
     * company_admin → same company.
     * employee      → same company + PM or creator.
     * finance       → no.
     */
    public function update(User $user, TaskProject $tp): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $tp);
        }

        if ($user->isEmployee()) {
            return $this->isSameCompany($user, $tp)
                && ($this->isProjectManager($user, $tp) || $this->isCreator($user, $tp));
        }

        return false;
    }

    /**
     * Same rules as update.
     */
    public function delete(User $user, TaskProject $tp): bool
    {
        return $this->update($user, $tp);
    }

    /**
     * viewKanban — same as view.
     */
    public function viewKanban(User $user, TaskProject $tp): bool
    {
        return $this->view($user, $tp);
    }

    /**
     * manageTasks — create/edit/delete tasks inside this project.
     *
     * holding_admin → any.
     * company_admin → same company.
     * employee      → same company + PM.
     * finance       → no.
     */
    public function manageTasks(User $user, TaskProject $tp): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $tp);
        }

        if ($user->isEmployee()) {
            return $this->isSameCompany($user, $tp)
                && $this->isProjectManager($user, $tp);
        }

        return false;
    }
}
