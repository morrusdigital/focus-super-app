<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'title',
        'is_completed',
        'position',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Get the checklist that owns the item.
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saved(function (ChecklistItem $item) {
            // Update card progress when checklist item is saved
            $item->checklist->card->updateProgress();
        });

        static::deleted(function (ChecklistItem $item) {
            // Update card progress when checklist item is deleted
            $item->checklist->card->updateProgress();
        });
    }
}
