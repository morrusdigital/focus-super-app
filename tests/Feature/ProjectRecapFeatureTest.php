<?php

namespace Tests\Feature;

use App\Models\ChartAccount;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectReceipt;
use App\Models\ProjectVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProjectRecapFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_view_only_own_projects_in_recap(): void
    {
        $companyA = Company::create([
            'name' => 'Company A',
            'type' => 'company',
        ]);
        $companyB = Company::create([
            'name' => 'Company B',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $companyA->id,
            'role' => 'admin_company',
        ]);

        Project::create([
            'company_id' => $companyA->id,
            'name' => 'Project Visible',
            'address' => 'Alamat A',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);
        Project::create([
            'company_id' => $companyB->id,
            'name' => 'Project Hidden',
            'address' => 'Alamat B',
            'contract_value' => 200000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('project-recaps.index'));

        $response->assertOk();
        $response->assertSee('Project Visible');
        $response->assertDontSee('Project Hidden');
    }

    public function test_finance_holding_can_view_only_child_company_projects_in_recap(): void
    {
        $holdingA = Company::create([
            'name' => 'Holding A',
            'type' => 'holding',
        ]);
        $holdingB = Company::create([
            'name' => 'Holding B',
            'type' => 'holding',
        ]);
        $childA1 = Company::create([
            'name' => 'Child A1',
            'type' => 'company',
            'parent_id' => $holdingA->id,
        ]);
        $childA2 = Company::create([
            'name' => 'Child A2',
            'type' => 'company',
            'parent_id' => $holdingA->id,
        ]);
        $childB = Company::create([
            'name' => 'Child B',
            'type' => 'company',
            'parent_id' => $holdingB->id,
        ]);

        $finance = User::factory()->create([
            'company_id' => $holdingA->id,
            'role' => 'finance_holding',
        ]);

        Project::create([
            'company_id' => $childA1->id,
            'name' => 'Project Child A1',
            'address' => 'Alamat A1',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);
        Project::create([
            'company_id' => $childA2->id,
            'name' => 'Project Child A2',
            'address' => 'Alamat A2',
            'contract_value' => 200000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);
        Project::create([
            'company_id' => $childB->id,
            'name' => 'Project Child B',
            'address' => 'Alamat B',
            'contract_value' => 300000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $response = $this->actingAs($finance)->get(route('project-recaps.index'));

        $response->assertOk();
        $response->assertSee('Project Child A1');
        $response->assertSee('Project Child A2');
        $response->assertDontSee('Project Child B');
    }

    public function test_recap_displays_formula_values_and_hpp_grouping_per_account(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 17, 10, 0, 0));

        $company = Company::create([
            'name' => 'Company Rekap',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Rekap',
            'address' => 'Alamat Rekap',
            'start_work_date' => '2026-01-01',
            'contract_value' => 100000000,
            'use_pph' => true,
            'pph_rate' => 2.5,
            'use_ppn' => true,
            'ppn_rate' => 11,
        ]);

        ProjectReceipt::create([
            'project_id' => $project->id,
            'receipt_date' => now()->toDateString(),
            'amount' => 30000000,
            'approval_status' => ProjectReceipt::APPROVAL_NOT_REQUIRED,
        ]);
        ProjectReceipt::create([
            'project_id' => $project->id,
            'receipt_date' => now()->toDateString(),
            'amount' => 5000000,
            'approval_status' => ProjectReceipt::APPROVAL_REJECTED,
        ]);

        $accountA = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6100',
            'name' => 'Biaya Material',
            'is_active' => true,
        ]);
        $accountB = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6200',
            'name' => 'Biaya Operasional',
            'is_active' => true,
        ]);

        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor Rekap',
        ]);

        ProjectExpense::create([
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $accountA->id,
            'expense_date' => now()->toDateString(),
            'item_name' => 'Material 1',
            'unit_price' => 1200000,
            'quantity' => 1,
            'unit' => 'lot',
            'amount' => 1200000,
        ]);
        ProjectExpense::create([
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $accountA->id,
            'expense_date' => now()->toDateString(),
            'item_name' => 'Material 2',
            'unit_price' => 800000,
            'quantity' => 1,
            'unit' => 'lot',
            'amount' => 800000,
        ]);
        ProjectExpense::create([
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $accountB->id,
            'expense_date' => now()->toDateString(),
            'item_name' => 'Ops',
            'unit_price' => 500000,
            'quantity' => 1,
            'unit' => 'lot',
            'amount' => 500000,
        ]);

        $otherProject = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Lain',
            'address' => 'Alamat Lain',
            'contract_value' => 200000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);
        $otherVendor = ProjectVendor::create([
            'project_id' => $otherProject->id,
            'name' => 'Vendor Lain',
        ]);
        ProjectExpense::create([
            'project_id' => $otherProject->id,
            'vendor_id' => $otherVendor->id,
            'chart_account_id' => $accountA->id,
            'expense_date' => now()->toDateString(),
            'item_name' => 'Should not be in Project Rekap row',
            'unit_price' => 999000,
            'quantity' => 1,
            'unit' => 'lot',
            'amount' => 999000,
        ]);

        $response = $this->actingAs($admin)->get(route('project-recaps.index'));

        $response->assertOk();
        $response->assertSee('Project Rekap');
        $response->assertSee('Rp 100.000.000,00');
        $response->assertSee('Rp 2.500.000,00');
        $response->assertSee('Rp 11.000.000,00');
        $response->assertSee('Rp 97.500.000,00');
        $response->assertSee('Rp 30.000.000,00');
        $response->assertSee('Rp 67.500.000,00');
        $response->assertSee('Rp 6.000.000,00');
        $response->assertSee('Rp 340.000,00');
        $response->assertSee('Rp 450.000,00');
        $response->assertSee('Rp 76.537.500,00');
        $response->assertDontSee('Rp 35.000.000,00');
        $response->assertSee('6100 - Biaya Material');
        $response->assertSee('6200 - Biaya Operasional');
        $response->assertSee('Rp 2.000.000,00');
        $response->assertSee('Rp 500.000,00');
        $response->assertSee('Total HPP');
        $response->assertSee('Rp 2.500.000,00');
        $response->assertSee('Prosentase Modal Kerja / HPP');
        $response->assertSee('3,27%');
        $response->assertSee('BALANCE DUE Project');
        $response->assertSee('Rp 73.697.500,00');

        Carbon::setTestNow();
    }

    public function test_recap_shows_dash_for_legacy_project_without_contract_fields(): void
    {
        $company = Company::create([
            'name' => 'Company Legacy',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        Project::create([
            'company_id' => $company->id,
            'name' => 'Legacy Project',
        ]);

        $response = $this->actingAs($admin)->get(route('project-recaps.index'));

        $response->assertOk();
        $response->assertSee('Legacy Project');

        $content = $response->getContent();
        preg_match('/<tr>.*?Legacy Project.*?<\/tr>/s', $content, $match);
        $this->assertNotEmpty($match);
        $row = $match[0];

        $this->assertMatchesRegularExpression('/Nilai Kontrak SPK:\s*<span class="fw-bold">\s*-\s*<\/span>/', $row);
        $this->assertMatchesRegularExpression('/PPH:\s*<span class="fw-bold">\s*-\s*<\/span>/', $row);
        $this->assertMatchesRegularExpression('/PPN:\s*<span class="fw-bold">\s*-\s*<\/span>/', $row);
        $this->assertMatchesRegularExpression('/Total Kontrak Net:\s*<span class="fw-bold">\s*-\s*<\/span>/', $row);
        $this->assertMatchesRegularExpression('/Outstanding:\s*<span class="fw-bold">\s*-\s*<\/span>/', $row);
        $this->assertMatchesRegularExpression('/<td class="text-end">\s*-\s*<\/td>/', $row);
    }
}
