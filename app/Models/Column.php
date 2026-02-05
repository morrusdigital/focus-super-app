<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'color',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Get the board that owns the column.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the cards for the column.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)->orderBy('position');
    }
}
