<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $casts = [
        'saldo_awal' => 'decimal:2',
    ];

    protected $fillable = [
        'name',
        'parent_id',
        'type',
        'saldo_awal',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(CompanyBankAccount::class);
    }
}
