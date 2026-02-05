<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id',
        'title',
        'description',
        'assignee_id',
        'position',
        'priority',
        'due_date',
        'progress',
        'labels',
    ];

    protected $casts = [
        'position' => 'integer',
        'due_date' => 'date',
        'progress' => 'decimal:2',
        'labels' => 'array',
    ];

    protected $appends = [
        'is_overdue',
    ];

    /**
     * Get the column that owns the card.
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    /**
     * Get the assignee of the card.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the checklists for the card.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }

    /**
     * Get all checklist items through checklists.
     */
    public function checklistItems()
    {
        return $this->hasManyThrough(ChecklistItem::class, Checklist::class);
    }

    /**
     * Check if the card is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast() && $this->progress < 100;
    }

    /**
     * Update card progress based on checklist items.
     */
    public function updateProgress(): void
    {
        $totalItems = $this->checklistItems()->count();

        if ($totalItems === 0) {
            $this->update(['progress' => 0]);
            return;
        }

        $completedItems = $this->checklistItems()->where('is_completed', true)->count();
        $progress = ($completedItems / $totalItems) * 100;

        $this->update(['progress' => round($progress, 2)]);
    }
}
