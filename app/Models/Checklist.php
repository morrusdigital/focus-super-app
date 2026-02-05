<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'title',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Get the card that owns the checklist.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the items for the checklist.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('position');
    }
}
