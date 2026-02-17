<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectReceipt extends Model
{
    use HasFactory;

    public const APPROVAL_NOT_REQUIRED = 'not_required';
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'project_id',
        'receipt_date',
        'amount',
        'source',
        'reference_no',
        'notes',
        'is_partial',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:2',
        'is_partial' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function allocations()
    {
        return $this->hasMany(ProjectReceiptAllocation::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getAllocatedAmountAttribute(): float
    {
        return (float) $this->allocations()->sum('amount');
    }

    public function getUnappliedAmountAttribute(): float
    {
        $unapplied = (float) $this->amount - $this->allocated_amount;

        return max(0, round($unapplied, 2));
    }
}
