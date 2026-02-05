<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    /**
     * Determine whether the user can view the card.
     */
    public function view(User $user, Card $card): bool
    {
        // User can view if they belong to the same company
        $project = $card->column->board->project;
        return $user->company_id === $project->company_id;
    }

    /**
     * Determine whether the user can create cards.
     */
    public function create(User $user): bool
    {
        // All users can create cards
        return true;
    }

    /**
     * Determine whether the user can update the card.
     */
    public function update(User $user, Card $card): bool
    {
        $project = $card->column->board->project;

        // Admin, project manager, or assigned user can update
        return $user->role === 'admin'
            || $user->id === $project->manager_id
            || $user->id === $card->assignee_id;
    }

    /**
     * Determine whether the user can delete the card.
     */
    public function delete(User $user, Card $card): bool
    {
        $project = $card->column->board->project;

        // Admin or project manager can delete
        return $user->role === 'admin' || $user->id === $project->manager_id;
    }

    /**
     * Determine whether the user can move the card.
     */
    public function move(User $user, Card $card): bool
    {
        $project = $card->column->board->project;

        // Admin, project manager, or assigned user can move
        return $user->role === 'admin'
            || $user->id === $project->manager_id
            || $user->id === $card->assignee_id;
    }
}
