<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ---------------------------------------------------------------
    // Legacy helpers — kept intact so existing policies/controllers
    // continue to work without modification.
    // ---------------------------------------------------------------

    public function isFinanceHolding(): bool
    {
        return $this->role === 'finance_holding';
    }

    public function isAdminCompany(): bool
    {
        return $this->role === 'admin_company';
    }

    // ---------------------------------------------------------------
    // New MVP role helpers — support both new role strings and the
    // legacy equivalents via UserRole::fromString() compatibility map.
    // Use these in new modules (Task, Kanban, etc.).
    // ---------------------------------------------------------------

    /**
     * True for 'holding_admin' and legacy 'finance_holding'.
     */
    public function isHoldingAdmin(): bool
    {
        return UserRole::isHoldingAdmin((string) $this->role);
    }

    /**
     * True for 'company_admin' and legacy 'admin_company'.
     */
    public function isCompanyAdmin(): bool
    {
        return UserRole::isCompanyAdmin((string) $this->role);
    }

    /**
     * True for 'project_manager'.
     */
    public function isProjectManager(): bool
    {
        return UserRole::isProjectManager((string) $this->role);
    }

    /**
     * True for 'member'.
     */
    public function isMember(): bool
    {
        return UserRole::isMember((string) $this->role);
    }

    // ---------------------------------------------------------------
    // Task & project membership relations (Task #3)
    // ---------------------------------------------------------------

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
                    ->using(ProjectMember::class)
                    ->withTimestamps();
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees')
                    ->using(TaskAssignee::class)
                    ->withTimestamps();
    }
}
