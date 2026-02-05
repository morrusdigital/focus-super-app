<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // User can view if they belong to the same company
        return $user->company_id === $project->company_id;
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Admin, or project manager can update
        return $user->role === 'admin' || $user->id === $project->manager_id;
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only admin or project manager can delete
        return $user->role === 'admin' || $user->id === $project->manager_id;
    }
}
