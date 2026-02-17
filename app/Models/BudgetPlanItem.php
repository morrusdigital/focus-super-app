<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetPlanItem extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Gaji',
        'Operasional',
        'Marketing',
        'Inventaris',
        'Other',
    ];

    protected $fillable = [
        'budget_plan_id',
        'project_id',
        'bank_account_id',
        'chart_account_id',
        'item_name',
        'vendor_name',
        'category',
        'unit_price',
        'quantity',
        'unit',
        'line_total',
        'real_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'line_total' => 'decimal:2',
        'real_amount' => 'decimal:2',
    ];

    public function budgetPlan()
    {
        return $this->belongsTo(BudgetPlan::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(CompanyBankAccount::class, 'bank_account_id');
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }
}
