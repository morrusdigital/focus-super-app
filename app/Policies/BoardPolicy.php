<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    /**
     * Determine whether the user can view the board.
     */
    public function view(User $user, Board $board): bool
    {
        // User can view if they belong to the same company as the project
        return $user->company_id === $board->project->company_id;
    }

    /**
     * Determine whether the user can update the board.
     */
    public function update(User $user, Board $board): bool
    {
        // Admin or project manager can update
        return $user->role === 'admin' || $user->id === $board->project->manager_id;
    }
}
