<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'position',
        'target_date',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'target_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the project that owns the milestone.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mark milestone as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark milestone as incomplete.
     */
    public function markAsIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
}
