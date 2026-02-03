<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPlanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_plan_id',
        'actor_id',
        'action',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function budgetPlan()
    {
        return $this->belongsTo(BudgetPlan::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
