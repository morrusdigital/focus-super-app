<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
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

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }
}
