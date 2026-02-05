<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'manager_id',
        'status',
        'start_date',
        'end_date',
        'budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    /**
     * Get the company that owns the project.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the manager of the project.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the board for the project.
     */
    public function board(): HasOne
    {
        return $this->hasOne(Board::class);
    }

    /**
     * Get the milestones for the project.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('position');
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::created(function (Project $project) {
            // Create board automatically
            $board = $project->board()->create([
                'name' => $project->name . ' Board',
                'description' => 'Kanban board for ' . $project->name,
            ]);

            // Create default columns
            $defaultColumns = [
                ['name' => 'Backlog', 'position' => 0, 'color' => '#6b7280'],
                ['name' => 'Ready', 'position' => 1, 'color' => '#3b82f6'],
                ['name' => 'To Do', 'position' => 2, 'color' => '#8b5cf6'],
                ['name' => 'In Progress', 'position' => 3, 'color' => '#f59e0b'],
                ['name' => 'Review', 'position' => 4, 'color' => '#ec4899'],
                ['name' => 'Blocked', 'position' => 5, 'color' => '#ef4444'],
                ['name' => 'Done', 'position' => 6, 'color' => '#10b981'],
            ];

            foreach ($defaultColumns as $column) {
                $board->columns()->create($column);
            }

            // Create default milestones
            $defaultMilestones = [
                ['name' => 'Inisiasi', 'position' => 0, 'description' => 'Project initiation phase'],
                ['name' => 'Perencanaan', 'position' => 1, 'description' => 'Planning phase'],
                ['name' => 'Eksekusi', 'position' => 2, 'description' => 'Execution phase'],
                ['name' => 'Monitoring', 'position' => 3, 'description' => 'Monitoring and control phase'],
                ['name' => 'Closing', 'position' => 4, 'description' => 'Project closing phase'],
            ];

            foreach ($defaultMilestones as $milestone) {
                $project->milestones()->create($milestone);
            }
        });
    }
}
