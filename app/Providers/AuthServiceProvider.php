<?php

namespace App\Providers;

use App\Models\BudgetPlan;
use App\Models\ChartAccount;
use App\Models\CompanyBankAccount;
use App\Models\Project;
use App\Models\BudgetPlanCategory;
use App\Models\TaxMaster;
use App\Policies\BudgetPlanPolicy;
use App\Policies\BudgetPlanCategoryPolicy;
use App\Policies\ChartAccountPolicy;
use App\Policies\CompanyBankAccountPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaxMasterPolicy;
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
        CompanyBankAccount::class => CompanyBankAccountPolicy::class,
        TaxMaster::class => TaxMasterPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
