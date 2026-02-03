<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_plan_id',
        'item_name',
        'kode',
        'vendor_name',
        'harsat',
        'qty',
        'satuan',
        'jumlah',
    ];

    protected $casts = [
        'harsat' => 'decimal:2',
        'qty' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    public function budgetPlan()
    {
        return $this->belongsTo(BudgetPlan::class);
    }
}
