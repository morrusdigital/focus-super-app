<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaskProjectTask extends Model
{
    protected $fillable = [
        'task_project_id',
        'company_id',
        'title',
        'status',
        'progress',
        'blocked_reason',
        'due_date',
    ];

    protected $casts = [
        'status'   => TaskStatus::class,
        'progress' => 'integer',
        'due_date' => 'date',
    ];

    // ---------------------------------------------------------------
    // Business rules — Issue #34
    //   • status done  ⇒ progress = 100
    //   • progress 100 ⇒ status  = done
    // Note: status blocked + blocked_reason validation is handled
    //       in the FormRequest layer.
    // ---------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        $applyRules = static function (self $task): void {
            // Resolve raw status string (enum or plain string)
            $statusValue = $task->status instanceof TaskStatus
                ? $task->status->value
                : (string) $task->status;

            if ($statusValue === TaskStatus::Done->value) {
                $task->progress = 100;
            } elseif ((int) $task->progress === 100) {
                $task->status = TaskStatus::Done->value;
            }
        };

        static::creating($applyRules);
        static::updating($applyRules);
    }

    // ---------------------------------------------------------------
    // Relations
    // ---------------------------------------------------------------

    public function taskProject(): BelongsTo
    {
        return $this->belongsTo(TaskProject::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'task_project_task_assignees',
            'task_project_task_id',
            'user_id'
        )->using(TaskProjectTaskAssignee::class)->withTimestamps();
    }
}
