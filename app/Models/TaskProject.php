<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'project_manager_id',
        'created_by',
    ];

    // ---------------------------------------------------------------
    // Relations
    // ---------------------------------------------------------------

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskProjectTask::class);
    }

    // ---------------------------------------------------------------
    // Backend summary (computed) — Issue #34
    // ---------------------------------------------------------------

    /**
     * Total number of tasks in this project.
     */
    public function totalTasks(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Number of tasks grouped by status.
     *
     * @return array{todo: int, doing: int, blocked: int, done: int}
     */
    public function tasksByStatus(): array
    {
        $counts = $this->tasks()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'todo'    => (int) ($counts['todo']    ?? 0),
            'doing'   => (int) ($counts['doing']   ?? 0),
            'blocked' => (int) ($counts['blocked'] ?? 0),
            'done'    => (int) ($counts['done']    ?? 0),
        ];
    }

    /**
     * Progress percent = done / total * 100.
     * Returns 0 if there are no tasks.
     */
    public function progressPercent(): int
    {
        $total = $this->totalTasks();

        if ($total === 0) {
            return 0;
        }

        $done = $this->tasks()
            ->where('status', TaskStatus::Done->value)
            ->count();

        return (int) round(($done / $total) * 100);
    }

    /**
     * Project status derived from task distribution.
     *
     * Rules:
     *  - no tasks          → not_started
     *  - all done          → done
     *  - any blocked       → blocked
     *  - any doing/done    → on_track
     *  - all todo          → not_started
     */
    public function projectStatus(): string
    {
        $total = $this->totalTasks();

        if ($total === 0) {
            return 'not_started';
        }

        $by = $this->tasksByStatus();

        if ($by['done'] === $total) {
            return 'done';
        }

        if ($by['blocked'] > 0) {
            return 'blocked';
        }

        if ($by['doing'] > 0 || $by['done'] > 0) {
            return 'on_track';
        }

        return 'not_started';
    }

    /**
     * Returns the full summary array for API / view consumption.
     */
    public function summary(): array
    {
        $by = $this->tasksByStatus();

        return [
            'total_tasks'      => $this->totalTasks(),
            'todo'             => $by['todo'],
            'doing'            => $by['doing'],
            'blocked'          => $by['blocked'],
            'done'             => $by['done'],
            'progress_percent' => $this->progressPercent(),
            'project_status'   => $this->projectStatus(),
        ];
    }
}
