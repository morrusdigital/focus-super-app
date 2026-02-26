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
        // Invoice proof attachment
        'invoice_proof_path',
        'invoice_proof_original_name',
        'invoice_proof_mime',
        'invoice_proof_size',
        'invoice_proof_uploaded_by',
        'invoice_proof_uploaded_at',
        // Bank mutation attachment
        'bank_mutation_path',
        'bank_mutation_original_name',
        'bank_mutation_mime',
        'bank_mutation_size',
        'bank_mutation_uploaded_by',
        'bank_mutation_uploaded_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
        'invoice_proof_uploaded_at' => 'datetime',
        'bank_mutation_uploaded_at' => 'datetime',
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
