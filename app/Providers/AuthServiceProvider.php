<?php

namespace App\Providers;

use App\Models\BudgetPlan;
use App\Models\ChartAccount;
use App\Models\CompanyBankAccount;
use App\Models\Project;
use App\Models\BudgetPlanCategory;
use App\Models\Task;
use App\Models\TaskProject;
use App\Models\TaskProjectTask;
use App\Models\TaxMaster;
use App\Models\RoleMenu;
use App\Models\User;
use App\Policies\BudgetPlanPolicy;
use App\Policies\RoleMenuPolicy;
use App\Policies\BudgetPlanCategoryPolicy;
use App\Policies\ChartAccountPolicy;
use App\Policies\CompanyBankAccountPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TaskProjectPolicy;
use App\Policies\TaskProjectTaskPolicy;
use App\Policies\TaxMasterPolicy;
use App\Policies\UserPolicy;
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
        BudgetPlanCategory::class => BudgetPlanCategoryPolicy::class,
        ChartAccount::class => ChartAccountPolicy::class,
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        TaskProject::class => TaskProjectPolicy::class,
        TaskProjectTask::class => TaskProjectTaskPolicy::class,
        CompanyBankAccount::class => CompanyBankAccountPolicy::class,
        TaxMaster::class => TaxMasterPolicy::class,
        User::class => UserPolicy::class,
        RoleMenu::class => RoleMenuPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
