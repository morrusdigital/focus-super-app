<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectReceiptAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_receipt_id',
        'project_term_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(ProjectReceipt::class, 'project_receipt_id');
    }

    public function term()
    {
        return $this->belongsTo(ProjectTerm::class, 'project_term_id');
    }
}
