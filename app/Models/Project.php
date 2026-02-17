<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'contract_value',
        'use_pph',
        'pph_tax_master_id',
        'pph_rate',
        'use_ppn',
        'ppn_tax_master_id',
        'ppn_rate',
        'unapplied_balance',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',
        'use_pph' => 'boolean',
        'pph_rate' => 'decimal:2',
        'use_ppn' => 'boolean',
        'ppn_rate' => 'decimal:2',
        'unapplied_balance' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function pphTaxMaster()
    {
        return $this->belongsTo(TaxMaster::class, 'pph_tax_master_id');
    }

    public function ppnTaxMaster()
    {
        return $this->belongsTo(TaxMaster::class, 'ppn_tax_master_id');
    }

    public function terms()
    {
        return $this->hasMany(ProjectTerm::class);
    }

    public function receipts()
    {
        return $this->hasMany(ProjectReceipt::class);
    }

    public function vendors()
    {
        return $this->hasMany(ProjectVendor::class);
    }

    public function expenses()
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function isTaxConfigurationComplete(): bool
    {
        if (blank($this->name) || blank($this->address) || $this->contract_value === null) {
            return false;
        }

        if ($this->use_pph === null || $this->use_ppn === null) {
            return false;
        }

        if ($this->use_pph && (!$this->pph_tax_master_id || $this->pph_rate === null)) {
            return false;
        }

        if ($this->use_ppn && (!$this->ppn_tax_master_id || $this->ppn_rate === null)) {
            return false;
        }

        return true;
    }

    public function getPphAmountAttribute(): ?float
    {
        if ($this->contract_value === null) {
            return null;
        }

        $contractValue = (float) $this->contract_value;

        if ($this->use_pph !== true || $this->pph_rate === null) {
            return 0.0;
        }

        $pphRate = (float) $this->pph_rate;

        return round($contractValue * ($pphRate / 100), 2);
    }

    public function getNetContractValueAttribute(): ?float
    {
        if ($this->contract_value === null) {
            return null;
        }

        $contractValue = (float) $this->contract_value;
        $pphAmount = $this->pph_amount ?? 0.0;

        return round($contractValue - $pphAmount, 2);
    }

    public function getPpnAmountAttribute(): ?float
    {
        if ($this->contract_value === null) {
            return null;
        }

        $contractValue = (float) $this->contract_value;

        if ($this->use_ppn !== true || $this->ppn_rate === null) {
            return 0.0;
        }

        $ppnRate = (float) $this->ppn_rate;

        return round($contractValue * ($ppnRate / 100), 2);
    }

    public function getContractValueWithPpnAttribute(): ?float
    {
        if ($this->contract_value === null) {
            return null;
        }

        $contractValue = (float) $this->contract_value;
        $ppnAmount = $this->ppn_amount ?? 0.0;

        return round($contractValue + $ppnAmount, 2);
    }

    public function getTotalTermAmountAttribute(): float
    {
        if ($this->relationLoaded('terms')) {
            return (float) $this->terms->sum('amount');
        }

        return (float) $this->terms()->sum('amount');
    }

    public function getTotalReceivedAmountAttribute(): float
    {
        if ($this->relationLoaded('receipts')) {
            return (float) $this->receipts
                ->where('approval_status', '!=', ProjectReceipt::APPROVAL_REJECTED)
                ->sum('amount');
        }

        return (float) $this->receipts()
            ->where('approval_status', '!=', ProjectReceipt::APPROVAL_REJECTED)
            ->sum('amount');
    }

    public function getOutstandingTotalAttribute(): float
    {
        if ($this->relationLoaded('terms')) {
            return (float) $this->terms->sum(fn (ProjectTerm $term) => $term->outstanding_amount);
        }

        return (float) $this->terms()->get()->sum(fn (ProjectTerm $term) => $term->outstanding_amount);
    }
}
