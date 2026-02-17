<?php

namespace Tests\Feature;

use App\Models\ChartAccount;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectExpenseFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_crud_project_expense_and_amount_is_computed_on_server(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();
        $project = $this->makeProject($company, 'Project Expense');
        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor A',
        ]);
        $chartAccount = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6100',
            'name' => 'Biaya Operasional',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('projects.expenses.store', $project), [
            'item_name' => 'Pembelian Material',
            'chart_account_id' => $chartAccount->id,
            'vendor_id' => $vendor->id,
            'expense_date' => now()->format('Y-m-d'),
            'unit_price' => 100000,
            'quantity' => 3,
            'unit' => 'pcs',
            'amount' => 1,
            'notes' => 'Catatan',
        ])->assertRedirect(route('projects.show', $project));

        $expense = ProjectExpense::firstOrFail();
        $this->assertDatabaseHas('project_expenses', [
            'id' => $expense->id,
            'project_id' => $project->id,
            'amount' => 300000.00,
        ]);

        $this->actingAs($admin)->put(route('projects.expenses.update', [$project, $expense]), [
            'item_name' => 'Pembelian Material Update',
            'chart_account_id' => $chartAccount->id,
            'vendor_id' => $vendor->id,
            'expense_date' => now()->format('Y-m-d'),
            'unit_price' => 125000,
            'quantity' => 4,
            'unit' => 'pcs',
            'amount' => 1,
            'notes' => 'Updated',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_expenses', [
            'id' => $expense->id,
            'item_name' => 'Pembelian Material Update',
            'amount' => 500000.00,
        ]);

        $this->actingAs($admin)->delete(route('projects.expenses.destroy', [$project, $expense]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_finance_holding_can_view_project_expense_but_cannot_create(): void
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

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);
        $finance = User::factory()->create([
            'company_id' => $holding->id,
            'role' => 'finance_holding',
        ]);

        $project = $this->makeProject($company, 'Project Child');
        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor Child',
        ]);
        $chartAccount = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6200',
            'name' => 'Biaya Project',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('projects.expenses.store', $project), [
            'item_name' => 'Sewa Alat',
            'chart_account_id' => $chartAccount->id,
            'vendor_id' => $vendor->id,
            'expense_date' => now()->format('Y-m-d'),
            'unit_price' => 200000,
            'quantity' => 2,
            'unit' => 'hari',
        ])->assertRedirect(route('projects.show', $project));

        $showResponse = $this->actingAs($finance)->get(route('projects.show', $project));
        $showResponse->assertOk();
        $showResponse->assertSee('Sewa Alat');

        $this->actingAs($finance)->post(route('projects.expenses.store', $project), [
            'item_name' => 'Tidak Boleh',
            'chart_account_id' => $chartAccount->id,
            'vendor_id' => $vendor->id,
            'expense_date' => now()->format('Y-m-d'),
            'unit_price' => 1,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertForbidden();
    }

    public function test_expense_validation_rejects_chart_account_other_company_and_vendor_other_project(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();
        $otherCompany = Company::create([
            'name' => 'Other Co',
            'type' => 'company',
        ]);

        $projectA = $this->makeProject($company, 'Project A');
        $projectB = $this->makeProject($company, 'Project B');

        $vendorB = ProjectVendor::create([
            'project_id' => $projectB->id,
            'name' => 'Vendor B',
        ]);

        $otherCompanyAccount = ChartAccount::create([
            'company_id' => $otherCompany->id,
            'code' => '9999',
            'name' => 'Akun Lain',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('projects.expenses.store', $projectA), [
            'item_name' => 'Invalid Expense',
            'chart_account_id' => $otherCompanyAccount->id,
            'vendor_id' => $vendorB->id,
            'expense_date' => now()->format('Y-m-d'),
            'unit_price' => 1000,
            'quantity' => 1,
            'unit' => 'unit',
        ]);

        $response->assertSessionHasErrors(['chart_account_id', 'vendor_id']);
    }

    public function test_expense_form_has_default_today_date(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();
        $project = $this->makeProject($company, 'Project Date');

        ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor Date',
        ]);
        ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6300',
            'name' => 'Akun Date',
            'is_active' => true,
        ]);

        $today = now()->format('Y-m-d');
        $response = $this->actingAs($admin)->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertSee('name="expense_date"', false);
        $response->assertSee('value="' . $today . '"', false);
    }

    private function makeCompanyAdmin(): array
    {
        $company = Company::create([
            'name' => 'Company',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        return [$company, $admin];
    }

    private function makeProject(Company $company, string $name): Project
    {
        return Project::create([
            'company_id' => $company->id,
            'name' => $name,
            'address' => 'Alamat ' . $name,
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);
    }
}
