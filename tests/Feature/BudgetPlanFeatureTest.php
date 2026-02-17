<?php

namespace Tests\Feature;

use App\Models\BudgetPlan;
use App\Models\BudgetPlanCategory;
use App\Models\ChartAccount;
use App\Models\Company;
use App\Models\CompanyBankAccount;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BudgetPlanFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_create_budget_plan_with_project_and_chart_account(): void
    {
        $company = Company::create([
            'name' => 'Child Co',
            'type' => 'company',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project A',
            'address' => 'Alamat',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $bankAccount = CompanyBankAccount::create([
            'company_id' => $company->id,
            'bank_name' => 'Bank ABC',
            'account_number' => '1234567890',
            'account_name' => 'PT Child',
        ]);

        $chartAccount = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6100',
            'name' => 'Biaya Operasional',
            'is_active' => true,
        ]);

        BudgetPlanCategory::create([
            'company_id' => $company->id,
            'name' => 'Operasional',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('budget-plans.store'), [
            'submission_date' => now()->format('Y-m-d'),
            'category' => 'Operasional',
            'notes' => 'Test BP',
            'items' => [[
                'project_id' => $project->id,
                'bank_account_id' => $bankAccount->id,
                'chart_account_id' => $chartAccount->id,
                'item_name' => 'Pembelian ATK',
                'vendor_name' => 'Vendor A',
                'category' => 'Operasional',
                'unit_price' => 100000,
                'quantity' => 2,
                'unit' => 'paket',
            ]],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('budget_plans', [
            'company_id' => $company->id,
            'requester_id' => $user->id,
            'category' => 'Operasional',
        ]);
        $this->assertDatabaseHas('budget_plan_items', [
            'project_id' => $project->id,
            'chart_account_id' => $chartAccount->id,
            'line_total' => 200000.00,
        ]);
    }

    public function test_admin_company_can_download_budget_plan_pdf(): void
    {
        $company = Company::create([
            'name' => 'Child Co',
            'type' => 'company',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $budgetPlan = BudgetPlan::create([
            'bp_number' => 'BP-TEST-00001',
            'company_id' => $company->id,
            'requester_id' => $user->id,
            'status' => BudgetPlan::STATUS_DRAFT,
            'total_amount' => 1000,
            'submission_date' => now()->toDateString(),
            'week_of_month' => 1,
            'project_count' => 0,
            'category' => 'Operasional',
        ]);

        $response = $this->actingAs($user)->get(route('budget-plans.pdf.show', $budgetPlan));

        $response->assertOk();
    }

    public function test_budget_plan_record_expense_route_is_removed(): void
    {
        $this->assertFalse(Route::has('budget-plans.record-expense'));
    }

    public function test_finance_holding_summary_uses_project_expenses_and_real_expense_form_is_removed(): void
    {
        $holding = Company::create([
            'name' => 'Holding',
            'type' => 'holding',
        ]);

        $company = Company::create([
            'name' => 'Child Co',
            'type' => 'company',
            'parent_id' => $holding->id,
            'saldo_awal' => 5000000,
        ]);

        $finance = User::factory()->create([
            'company_id' => $holding->id,
            'role' => 'finance_holding',
        ]);

        $requester = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Expense',
            'address' => 'Alamat',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $chartAccount = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6200',
            'name' => 'Biaya Proyek',
            'is_active' => true,
        ]);

        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor Proyek',
        ]);

        ProjectExpense::create([
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $chartAccount->id,
            'expense_date' => now()->toDateString(),
            'item_name' => 'Sewa Alat',
            'unit_price' => 250000,
            'quantity' => 2,
            'unit' => 'hari',
            'amount' => 500000,
            'notes' => 'Test',
        ]);

        $budgetPlan = BudgetPlan::create([
            'bp_number' => 'BP-TEST-00002',
            'company_id' => $company->id,
            'requester_id' => $requester->id,
            'status' => BudgetPlan::STATUS_APPROVED,
            'approved_at' => now(),
            'total_amount' => 1000000,
            'submission_date' => now()->toDateString(),
            'week_of_month' => 1,
            'project_count' => 1,
            'category' => 'Operasional',
        ]);

        $response = $this->actingAs($finance)->get(route('budget-plans.show', $budgetPlan));

        $response->assertOk();
        $response->assertSee('Total Real Expense');
        $response->assertSee('Rp 500.000,00');
        $response->assertDontSee('Input Real Expense');
    }
}
