<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectExpense extends Model
{
    use HasFactory;

    public const SOURCE_MANUAL_PROJECT = 'manual_project';
    public const SOURCE_BUDGET_PLAN_REALIZATION = 'budget_plan_realization';

    protected $fillable = [
        'project_id',
        'budget_plan_id',
        'budget_plan_item_id',
        'expense_source',
        'vendor_id',
        'chart_account_id',
        'expense_date',
        'item_name',
        'unit_price',
        'quantity',
        'unit',
        'amount',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor()
    {
        return $this->belongsTo(ProjectVendor::class, 'vendor_id');
    }

    public function budgetPlan()
    {
        return $this->belongsTo(BudgetPlan::class);
    }

    public function budgetPlanItem()
    {
        return $this->belongsTo(BudgetPlanItem::class);
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }
}
