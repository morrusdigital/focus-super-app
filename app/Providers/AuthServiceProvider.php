<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\BudgetPlan;
use App\Models\Card;
use App\Models\Project;
use App\Policies\BoardPolicy;
use App\Policies\BudgetPlanPolicy;
use App\Policies\CardPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        BudgetPlan::class => BudgetPlanPolicy::class,
        Project::class => ProjectPolicy::class,
        Board::class => BoardPolicy::class,
        Card::class => CardPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
