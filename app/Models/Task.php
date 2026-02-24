<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'project_id',
        'title',
        'description',
        'status',
        'progress',
        'blocked_reason',
        'due_date',
        'milestone',
        'wbs',
        'priority',
        'start_date',
    ];

    protected $casts = [
        'status'     => TaskStatus::class,
        'progress'   => 'integer',
        'start_date' => 'date',
        'due_date'   => 'date',
    ];

    // ---------------------------------------------------------------
    // Relations
    // ---------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')
                    ->using(TaskAssignee::class)
                    ->withTimestamps();
    }
}
