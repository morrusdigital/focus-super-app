<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProgress extends Model
{
    use HasFactory;

    protected $table = 'project_progresses';

    protected $fillable = [
        'project_id',
        'progress_date',
        'progress_percent',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'progress_date' => 'date',
        'progress_percent' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
