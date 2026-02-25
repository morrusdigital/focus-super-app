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
     *   PM (project_manager_id) or project member → can see tasks
     *   admin roles → NO access (they manage projects, not tasks)
     */
    public function view(User $user, Task $task): bool
    {
        return $this->isProjectManagerOfTask($user, $task)
            || $this->isMemberOfTaskProject($user, $task);
    }

    /**
     * update:
     *   PM (project_manager_id) → can update tasks in their project
     *   members / admin roles → NOT allowed
     */
    public function update(User $user, Task $task): bool
    {
        return $this->isProjectManagerOfTask($user, $task);
    }

    /**
     * markDone:
     *   PM (project_manager_id) → tasks in their project
     *   project member who is assignee → only their own assigned task
     *   admin roles → NOT allowed
     */
    public function markDone(User $user, Task $task): bool
    {
        if ($this->isProjectManagerOfTask($user, $task)) {
            return true;
        }

        if ($this->isMemberOfTaskProject($user, $task)) {
            return $this->isAssigneeOfTask($user, $task);
        }

        return false;
    }
}
