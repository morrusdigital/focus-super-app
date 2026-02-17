<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTerm extends Model
{
    use HasFactory;

    public const BASIS_NOMINAL = 'nominal';
    public const BASIS_PERCENTAGE = 'percentage';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'project_id',
        'sequence_no',
        'name',
        'basis_type',
        'percentage',
        'amount',
        'invoice_number',
        'invoice_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'invoice_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function allocations()
    {
        return $this->hasMany(ProjectReceiptAllocation::class);
    }

    public function getEffectiveAllocatedAmountAttribute(): float
    {
        return (float) $this->allocations()
            ->whereHas('receipt', function ($query) {
                $query->where('approval_status', '!=', ProjectReceipt::APPROVAL_REJECTED);
            })
            ->sum('amount');
    }

    public function getOutstandingAmountAttribute(): float
    {
        $outstanding = (float) $this->amount - $this->effective_allocated_amount;

        return max(0, round($outstanding, 2));
    }
}
