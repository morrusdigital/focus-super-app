<?php

namespace Tests\Feature;

use App\Models\BudgetPlan;
use App\Models\BudgetPlanItem;
use App\Models\Company;
use App\Models\CompanyBankAccount;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetPlanFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_create_budget_plan_with_project_category_and_bank_account(): void
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
        ]);

        $bankAccount = CompanyBankAccount::create([
            'company_id' => $company->id,
            'bank_name' => 'Bank ABC',
            'account_number' => '1234567890',
            'account_name' => 'PT Child',
        ]);

        $payload = [
            'tanggal' => now()->format('Y-m-d'),
            'notes' => 'Test BP',
            'bank_account_id' => $bankAccount->id,
            'items' => [[
                'project_id' => $project->id,
                'item_name' => 'Gaji Staff',
                'kode' => 'GJ-001',
                'vendor_name' => 'Vendor A',
                'category' => 'Gaji',
                'harsat' => 1000,
                'qty' => 2,
                'satuan' => 'orang',
            ]],
        ];

        $response = $this->actingAs($user)->post(route('budget-plans.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('budget_plans', [
            'company_id' => $company->id,
            'bank_account_id' => $bankAccount->id,
        ]);
        $this->assertDatabaseHas('budget_plan_items', [
            'project_id' => $project->id,
            'category' => 'Gaji',
            'item_name' => 'Gaji Staff',
        ]);
    }

    public function test_finance_holding_can_record_real_expense_on_approved_budget_plan(): void
    {
        $holding = Company::create([
            'name' => 'Holding',
            'type' => 'holding',
        ]);

        $company = Company::create([
            'name' => 'Child Co',
            'type' => 'company',
            'parent_id' => $holding->id,
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
            'name' => 'Project B',
        ]);

        $bankAccount = CompanyBankAccount::create([
            'company_id' => $company->id,
            'bank_name' => 'Bank XYZ',
            'account_number' => '999888777',
            'account_name' => 'PT Child',
        ]);

        $budgetPlan = BudgetPlan::create([
            'bp_number' => 'BP-TEST-00001',
            'company_id' => $company->id,
            'bank_account_id' => $bankAccount->id,
            'requester_id' => $requester->id,
            'status' => BudgetPlan::STATUS_APPROVED,
            'approved_at' => now(),
            'total_amount' => 2000,
            'tanggal' => now()->toDateString(),
        ]);

        $item = BudgetPlanItem::create([
            'budget_plan_id' => $budgetPlan->id,
            'project_id' => $project->id,
            'item_name' => 'Marketing Ads',
            'kode' => 'MK-01',
            'vendor_name' => 'Vendor B',
            'category' => 'Marketing',
            'harsat' => 1000,
            'qty' => 2,
            'satuan' => 'paket',
            'jumlah' => 2000,
            'real_amount' => 0,
        ]);

        $response = $this->actingAs($finance)->post(route('budget-plans.record-expense', $budgetPlan), [
            'items' => [
                $item->id => ['real_amount' => 1500],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('budget_plan_items', [
            'id' => $item->id,
            'real_amount' => 1500,
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

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project PDF',
        ]);

        $bankAccount = CompanyBankAccount::create([
            'company_id' => $company->id,
            'bank_name' => 'Bank PDF',
            'account_number' => '111222333',
            'account_name' => 'PT Child',
        ]);

        $budgetPlan = BudgetPlan::create([
            'bp_number' => 'BP-TEST-00002',
            'company_id' => $company->id,
            'bank_account_id' => $bankAccount->id,
            'requester_id' => $user->id,
            'status' => BudgetPlan::STATUS_DRAFT,
            'total_amount' => 1000,
            'tanggal' => now()->toDateString(),
        ]);

        BudgetPlanItem::create([
            'budget_plan_id' => $budgetPlan->id,
            'project_id' => $project->id,
            'item_name' => 'Operasional',
            'kode' => 'OP-01',
            'vendor_name' => 'Vendor C',
            'category' => 'Operasional',
            'harsat' => 1000,
            'qty' => 1,
            'satuan' => 'paket',
            'jumlah' => 1000,
            'real_amount' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('budget-plans.pdf.show', $budgetPlan));

        $response->assertOk();
    }
}
