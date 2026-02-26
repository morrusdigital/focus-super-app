<?php

namespace App\Policies;

use App\Models\TaskProjectTask;
use App\Models\User;

/**
 * Policy for individual tasks inside a Task Project (Issue #35).
 */
class TaskProjectTaskPolicy
{
    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function isSameCompany(User $user, TaskProjectTask $task): bool
    {
        return (int) $task->company_id === (int) $user->company_id;
    }

    private function isProjectManager(User $user, TaskProjectTask $task): bool
    {
        $pmId = $task->taskProject?->project_manager_id;
        return $pmId !== null && (int) $pmId === (int) $user->id;
    }

    private function isAssignee(User $user, TaskProjectTask $task): bool
    {
        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    // ---------------------------------------------------------------
    // Abilities
    // ---------------------------------------------------------------

    /**
     * update / delete — PM, company_admin, holding_admin only.
     * finance & non-PM employee cannot edit task data.
     */
    public function update(User $user, TaskProjectTask $task): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $task);
        }

        if ($user->isEmployee()) {
            return $this->isSameCompany($user, $task)
                && $this->isProjectManager($user, $task);
        }

        return false;
    }

    public function delete(User $user, TaskProjectTask $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * moveStatus — PM or task assignee (same company).
     * Finance → no.
     */
    public function moveStatus(User $user, TaskProjectTask $task): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $task);
        }

        if ($user->isEmployee()) {
            return $this->isSameCompany($user, $task)
                && ($this->isProjectManager($user, $task) || $this->isAssignee($user, $task));
        }

        return false;
    }

    /**
     * markDone — same as moveStatus.
     */
    public function markDone(User $user, TaskProjectTask $task): bool
    {
        return $this->moveStatus($user, $task);
    }
}
