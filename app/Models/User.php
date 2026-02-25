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
    // Final role helpers — use these in all new modules.
    // PM / member status is determined by the project model, not roles.
    // ---------------------------------------------------------------

    public function isHoldingAdmin(): bool
    {
        return UserRole::isHoldingAdmin((string) $this->role);
    }

    public function isCompanyAdmin(): bool
    {
        return UserRole::isCompanyAdmin((string) $this->role);
    }

    public function isFinanceHolding(): bool
    {
        return UserRole::isFinanceHolding((string) $this->role);
    }

    public function isFinanceCompany(): bool
    {
        return UserRole::isFinanceCompany((string) $this->role);
    }

    public function isEmployee(): bool
    {
        return UserRole::isEmployee((string) $this->role);
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
