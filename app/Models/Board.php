<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
    ];

    /**
     * Get the project that owns the board.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the columns for the board.
     */
    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    /**
     * Get all cards through columns.
     */
    public function cards()
    {
        return $this->hasManyThrough(Card::class, Column::class);
    }
}
