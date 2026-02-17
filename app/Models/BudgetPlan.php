<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPlan extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_REVISION_REQUESTED,
    ];

    protected $fillable = [
        'bp_number',
        'company_id',
        'requester_id',
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'revision_requested_at',
        'total_amount',
        'notes',
        'submission_date',
        'week_of_month',
        'project_count',
        'category',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'submission_date' => 'date',
        'week_of_month' => 'integer',
        'project_count' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function items()
    {
        return $this->hasMany(BudgetPlanItem::class);
    }

    public function logs()
    {
        return $this->hasMany(BudgetPlanLog::class);
    }

    public function realizations()
    {
        return $this->hasMany(ProjectExpense::class)
            ->where('expense_source', ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION);
    }
}
