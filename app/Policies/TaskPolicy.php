<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    /**
     * Task belongs to the same company as the user.
     */
    private function isSameCompany(User $user, Task $task): bool
    {
        return (int) $task->company_id === (int) $user->company_id;
    }

    /**
     * User is the project manager of the task's project.
     */
    private function isProjectManagerOfTask(User $user, Task $task): bool
    {
        $managerId = $task->project?->project_manager_id;
        return $managerId !== null && (int) $managerId === (int) $user->id;
    }

    /**
     * User is a member of the task's project (via project_members pivot).
     */
    private function isMemberOfTaskProject(User $user, Task $task): bool
    {
        return $task->project?->members()->where('users.id', $user->id)->exists() ?? false;
    }

    /**
     * User is an assignee of this task (via task_assignees pivot).
     */
    private function isAssigneeOfTask(User $user, Task $task): bool
    {
        return $task->assignees()->where('users.id', $user->id)->exists();
    }

    // ---------------------------------------------------------------
    // Abilities
    // ---------------------------------------------------------------

    /**
     * view:
     *   holding_admin  → all tasks
     *   company_admin  → tasks in own company
     *   project_manager → tasks in projects they manage
     *   member         → tasks in projects they have joined
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $task);
        }

        if ($user->isProjectManager()) {
            return $this->isProjectManagerOfTask($user, $task);
        }

        if ($user->isMember()) {
            return $this->isMemberOfTaskProject($user, $task);
        }

        return false;
    }

    /**
     * update:
     *   holding_admin  → all tasks
     *   company_admin  → tasks in own company
     *   project_manager → tasks in projects they manage
     *   member         → not allowed
     */
    public function update(User $user, Task $task): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $task);
        }

        if ($user->isProjectManager()) {
            return $this->isProjectManagerOfTask($user, $task);
        }

        return false;
    }

    /**
     * markDone:
     *   holding_admin  → all tasks
     *   company_admin  → tasks in own company
     *   project_manager → tasks in projects they manage
     *   member         → only if they are an assignee of the task
     */
    public function markDone(User $user, Task $task): bool
    {
        if ($user->isHoldingAdmin()) {
            return true;
        }

        if ($user->isCompanyAdmin()) {
            return $this->isSameCompany($user, $task);
        }

        if ($user->isProjectManager()) {
            return $this->isProjectManagerOfTask($user, $task);
        }

        if ($user->isMember()) {
            return $this->isAssigneeOfTask($user, $task);
        }

        return false;
    }
}
