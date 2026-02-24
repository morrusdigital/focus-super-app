<?php

namespace App\Observers;

use App\Enums\TaskStatus;
use App\Models\Task;

class TaskObserver
{
    /**
     * Recalculate project progress when a new task is created.
     * A new task is never "done", so done_tasks stays the same but
     * total_tasks increases → percentage drops.
     */
    public function created(Task $task): void
    {
        $task->project?->recalculateProgress();
    }

    /**
     * Recalculate project progress when a task is deleted.
     * Removing a task changes the denominator (and possibly the numerator).
     */
    public function deleted(Task $task): void
    {
        $task->project?->recalculateProgress();
    }

    /**
     * Recalculate project progress only when the status column changes
     * and the change involves the "done" state (entering or leaving it).
     *
     * Non-done ↔ non-done transitions (e.g. todo→doing) do NOT affect the
     * done ratio so we intentionally skip them.
     */
    public function updated(Task $task): void
    {
        if (! $task->wasChanged('status')) {
            return;
        }

        // getOriginal() may return raw string or enum depending on Laravel version.
        $oldRaw    = $task->getOriginal('status');
        $oldStatus = $oldRaw instanceof \BackedEnum ? $oldRaw->value : (string) $oldRaw;

        $newStatus = $task->status instanceof \BackedEnum
            ? $task->status->value
            : (string) $task->status;

        $doneValue = TaskStatus::Done->value; // 'done'

        if ($oldStatus === $doneValue || $newStatus === $doneValue) {
            $task->project?->recalculateProgress();
        }
    }
}
