<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    public const COMPANY_RIGHT_RATE = 0.20;
    public const FEE_SELES_RATE = 0.015;
    public const WORKING_CAPITAL_RATE = 0.785;
    public const DISCONTO_RATE = 0.10;

    protected $fillable = [
        'company_id',
        'project_manager_id',
        'name',
        'address',
        'start_work_date',
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
        'start_work_date' => 'date',
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

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->using(ProjectMember::class)
                    ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
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

    public function progresses()
    {
        return $this->hasMany(ProjectProgress::class);
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

    public function getOutstandingContractValueAttribute(): ?float
    {
        if ($this->net_contract_value === null) {
            return null;
        }

        $outstanding = (float) $this->net_contract_value - $this->total_received_amount;

        return max(0, round($outstanding, 2));
    }

    public function getCompanyRightAmountAttribute(): float
    {
        return round($this->total_received_amount * self::COMPANY_RIGHT_RATE, 2);
    }

    public function getFeeSelesAmountAttribute(): float
    {
        return round($this->total_received_amount * self::FEE_SELES_RATE, 2);
    }

    public function getWorkingCapitalAmountAttribute(): ?float
    {
        if ($this->net_contract_value === null) {
            return null;
        }

        return round((float) $this->net_contract_value * self::WORKING_CAPITAL_RATE, 2);
    }

    public function getDiscontoAmountAttribute(): ?float
    {
        if ($this->working_days_elapsed === null) {
            return null;
        }

        $daysElapsed = $this->working_days_elapsed;
        if ($daysElapsed <= 30) {
            return 0.0;
        }

        $base = $this->company_right_amount * self::DISCONTO_RATE;
        $factor = ($daysElapsed / 30) - 1;

        return round($base * $factor, 2);
    }

    public function getWorkingDaysElapsedAttribute(): ?int
    {
        if ($this->start_work_date === null) {
            return null;
        }

        $days = (int) $this->start_work_date->startOfDay()->diffInDays(now()->startOfDay(), false);

        return max(0, $days);
    }
}
