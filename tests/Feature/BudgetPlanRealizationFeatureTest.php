<?php

namespace Tests\Feature;

use App\Models\BudgetPlan;
use App\Models\BudgetPlanItem;
use App\Models\ChartAccount;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BudgetPlanRealizationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_owner_can_store_realization_and_lock_item_fields_from_budget_plan_item(): void
    {
        [$company, $admin, $project, $chartAccount, $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor Existing',
        ]);

        $otherProject = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Lain',
            'address' => 'Alamat Lain',
            'contract_value' => 200000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);
        $otherAccount = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '9999',
            'name' => 'Akun Lain',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_id' => $vendor->id,
            'project_id' => $otherProject->id,
            'chart_account_id' => $otherAccount->id,
            'item_name' => 'Manipulated',
            'unit_price' => 400000,
            'quantity' => 2,
            'unit' => 'pcs',
            'notes' => 'Realisasi 1',
        ])->assertRedirect(route('budget-plans.show', $budgetPlan));

        $this->assertDatabaseHas('project_expenses', [
            'budget_plan_id' => $budgetPlan->id,
            'budget_plan_item_id' => $item->id,
            'expense_source' => ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION,
            'project_id' => $item->project_id,
            'chart_account_id' => $item->chart_account_id,
            'item_name' => $item->item_name,
            'vendor_id' => $vendor->id,
            'amount' => 800000,
        ]);
    }

    public function test_realisasi_can_create_new_vendor_and_link_it(): void
    {
        [, $admin, $project, , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Baru Otomatis',
            'unit_price' => 100000,
            'quantity' => 3,
            'unit' => 'hari',
        ])->assertRedirect(route('budget-plans.show', $budgetPlan));

        $vendor = ProjectVendor::query()
            ->where('project_id', $project->id)
            ->where('name', 'Vendor Baru Otomatis')
            ->firstOrFail();

        $this->assertDatabaseHas('project_expenses', [
            'budget_plan_id' => $budgetPlan->id,
            'budget_plan_item_id' => $item->id,
            'vendor_id' => $vendor->id,
            'expense_source' => ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION,
        ]);
    }

    public function test_authorization_and_approved_status_are_enforced_for_realisasi(): void
    {
        [$company, $adminOwner, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $budgetPlan->update(['status' => BudgetPlan::STATUS_DRAFT, 'approved_at' => null]);

        $this->actingAs($adminOwner)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Draft',
            'unit_price' => 100000,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertForbidden();

        $budgetPlan->update(['status' => BudgetPlan::STATUS_APPROVED, 'approved_at' => now()]);

        $holding = Company::create([
            'name' => 'Holding',
            'type' => 'holding',
        ]);
        $child = Company::create([
            'name' => 'Child',
            'type' => 'company',
            'parent_id' => $holding->id,
        ]);
        $finance = User::factory()->create([
            'company_id' => $holding->id,
            'role' => 'finance_holding',
        ]);

        $otherCompany = Company::create([
            'name' => 'Other Company',
            'type' => 'company',
        ]);
        $otherAdmin = User::factory()->create([
            'company_id' => $otherCompany->id,
            'role' => 'admin_company',
        ]);

        $this->actingAs($finance)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Finance',
            'unit_price' => 100000,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertForbidden();

        $this->actingAs($otherAdmin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Other',
            'unit_price' => 100000,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertForbidden();
    }

    public function test_realisasi_can_be_updated_and_deleted(): void
    {
        [, $admin, $project, $chartAccount, $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor X',
        ]);

        $realization = ProjectExpense::create([
            'project_id' => $project->id,
            'budget_plan_id' => $budgetPlan->id,
            'budget_plan_item_id' => $item->id,
            'expense_source' => ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $chartAccount->id,
            'expense_date' => now()->toDateString(),
            'item_name' => $item->item_name,
            'unit_price' => 500000,
            'quantity' => 1,
            'unit' => 'unit',
            'amount' => 500000,
            'notes' => 'Awal',
        ]);

        // Update within budget: 500000 → 800000 (both ≤ line_total 1000000)
        $this->actingAs($admin)->put(route('budget-plans.realizations.update', [$budgetPlan, $realization]), [
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Y',
            'unit_price' => 800000,
            'quantity' => 1,
            'unit' => 'unit',
            'notes' => 'Update',
        ])->assertRedirect(route('budget-plans.show', $budgetPlan));

        $realization->refresh();
        $this->assertSame('800000.00', (string) $realization->amount);
        $this->assertSame('Update', $realization->notes);
        $this->assertSame(ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION, $realization->expense_source);

        $this->actingAs($admin)->delete(route('budget-plans.realizations.destroy', [$budgetPlan, $realization]))
            ->assertRedirect(route('budget-plans.show', $budgetPlan));

        $this->assertDatabaseMissing('project_expenses', [
            'id' => $realization->id,
        ]);
    }

    public function test_realisasi_shows_over_budget_warning(): void
    {
        [, $admin, $project, $chartAccount, $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor Over Budget',
        ]);

        // Directly insert an over-budget record to simulate legacy/edge-case data
        ProjectExpense::create([
            'project_id' => $project->id,
            'budget_plan_id' => $budgetPlan->id,
            'budget_plan_item_id' => $item->id,
            'expense_source' => ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $chartAccount->id,
            'expense_date' => now()->toDateString(),
            'item_name' => $item->item_name,
            'unit_price' => 1200000,
            'quantity' => 1,
            'unit' => 'unit',
            'amount' => 1200000,
        ]);

        $showResponse = $this->actingAs($admin)->get(route('budget-plans.show', $budgetPlan));
        $showResponse->assertOk();
        $showResponse->assertSee('Over Budget');
        $showResponse->assertSee('Selisih: Rp 200.000,00');
    }

    public function test_project_expense_from_bp_realisasi_is_read_only_in_project_module(): void
    {
        [, $admin, $project, $chartAccount, $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $vendor = ProjectVendor::create([
            'project_id' => $project->id,
            'name' => 'Vendor Z',
        ]);

        $realization = ProjectExpense::create([
            'project_id' => $project->id,
            'budget_plan_id' => $budgetPlan->id,
            'budget_plan_item_id' => $item->id,
            'expense_source' => ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $chartAccount->id,
            'expense_date' => now()->toDateString(),
            'item_name' => $item->item_name,
            'unit_price' => 100000,
            'quantity' => 2,
            'unit' => 'unit',
            'amount' => 200000,
        ]);

        $projectShow = $this->actingAs($admin)->get(route('projects.show', $project));
        $projectShow->assertOk();
        $projectShow->assertSee('BP Realisasi');
        $projectShow->assertSee('Kelola dari modul Realisasi BP');

        $this->actingAs($admin)->put(route('projects.expenses.update', [$project, $realization]), [
            'item_name' => 'Edit',
            'chart_account_id' => $chartAccount->id,
            'vendor_id' => $vendor->id,
            'expense_date' => now()->toDateString(),
            'unit_price' => 10,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertSessionHasErrors(['expense']);

        $this->actingAs($admin)->delete(route('projects.expenses.destroy', [$project, $realization]))
            ->assertSessionHasErrors(['expense']);
    }

    public function test_create_over_limit_is_rejected(): void
    {
        [, $admin, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();
        // item->line_total = 1000000

        $response = $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Over',
            'unit_price' => 600000,
            'quantity' => 2, // 1200000 > 1000000
            'unit' => 'unit',
        ]);

        $response->assertSessionHasErrors(['unit_price']);
        $this->assertDatabaseEmpty('project_expenses');
    }

    public function test_update_over_limit_is_rejected(): void
    {
        [, $admin, $project, $chartAccount, $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();
        // item->line_total = 1000000

        $vendor = ProjectVendor::create(['project_id' => $project->id, 'name' => 'Vendor A']);

        $realization = ProjectExpense::create([
            'project_id' => $project->id,
            'budget_plan_id' => $budgetPlan->id,
            'budget_plan_item_id' => $item->id,
            'expense_source' => ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION,
            'vendor_id' => $vendor->id,
            'chart_account_id' => $chartAccount->id,
            'expense_date' => now()->toDateString(),
            'item_name' => $item->item_name,
            'unit_price' => 500000,
            'quantity' => 1,
            'unit' => 'unit',
            'amount' => 500000,
        ]);

        // Try to update to 1200000 (exceeds line_total 1000000 when excluding this record: remaining=1000000)
        $response = $this->actingAs($admin)->put(route('budget-plans.realizations.update', [$budgetPlan, $realization]), [
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor B',
            'unit_price' => 600000,
            'quantity' => 2, // 1200000 > 1000000
            'unit' => 'unit',
        ]);

        $response->assertSessionHasErrors(['unit_price']);
        $this->assertSame('500000.00', (string) $realization->fresh()->amount);
    }

    public function test_create_exact_remaining_is_accepted(): void
    {
        [, $admin, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();
        // item->line_total = 1000000; exact same as remaining should be accepted

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Exact',
            'unit_price' => 1000000,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertRedirect(route('budget-plans.show', $budgetPlan));

        $this->assertDatabaseHas('project_expenses', [
            'budget_plan_item_id' => $item->id,
            'amount' => 1000000,
        ]);
    }

    public function test_parallel_transactions_do_not_cause_over_realization(): void
    {
        [, $admin, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();
        // item->line_total = 1000000

        // First request: 600000 – succeeds
        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor P1',
            'unit_price' => 600000,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertRedirect(route('budget-plans.show', $budgetPlan));

        // Second request: 600000 more → total 1200000 > 1000000 – must be rejected
        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor P2',
            'unit_price' => 600000,
            'quantity' => 1,
            'unit' => 'unit',
        ])->assertSessionHasErrors(['unit_price']);

        // Total must not exceed line_total
        $total = ProjectExpense::where('budget_plan_item_id', $item->id)
            ->where('expense_source', ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION)
            ->sum('amount');

        $this->assertEquals(600000, $total);
    }

    public function test_create_realization_with_attachment_files(): void
    {
        Storage::fake('local');

        [, $admin, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $invoiceFile = UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf');
        $mutationFile = UploadedFile::fake()->create('mutasi.jpg', 200, 'image/jpeg');

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor File',
            'unit_price' => 500000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $invoiceFile,
            'bank_mutation_file' => $mutationFile,
        ])->assertRedirect(route('budget-plans.show', $budgetPlan));

        $expense = ProjectExpense::where('budget_plan_id', $budgetPlan->id)->firstOrFail();

        $this->assertNotNull($expense->invoice_proof_path);
        $this->assertSame('nota.pdf', $expense->invoice_proof_original_name);
        $this->assertNotNull($expense->invoice_proof_uploaded_at);
        $this->assertSame($admin->id, $expense->invoice_proof_uploaded_by);

        $this->assertNotNull($expense->bank_mutation_path);
        $this->assertSame('mutasi.jpg', $expense->bank_mutation_original_name);
        $this->assertNotNull($expense->bank_mutation_uploaded_at);

        Storage::assertExists($expense->invoice_proof_path);
        Storage::assertExists($expense->bank_mutation_path);
    }

    public function test_update_realization_replaces_old_attachment_files(): void
    {
        Storage::fake('local');

        [, $admin, $project, $chartAccount, $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $vendor = ProjectVendor::create(['project_id' => $project->id, 'name' => 'Vendor File']);

        $oldInvoice = UploadedFile::fake()->create('old_nota.pdf', 50, 'application/pdf');
        $oldMutation = UploadedFile::fake()->create('old_mutasi.pdf', 50, 'application/pdf');

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_id' => $vendor->id,
            'unit_price' => 300000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $oldInvoice,
            'bank_mutation_file' => $oldMutation,
        ])->assertRedirect();

        $expense = ProjectExpense::where('budget_plan_id', $budgetPlan->id)->firstOrFail();
        $oldInvoicePath = $expense->invoice_proof_path;
        $oldMutationPath = $expense->bank_mutation_path;

        Storage::assertExists($oldInvoicePath);
        Storage::assertExists($oldMutationPath);

        $newInvoice = UploadedFile::fake()->create('new_nota.png', 80, 'image/png');
        $newMutation = UploadedFile::fake()->create('new_mutasi.jpg', 80, 'image/jpeg');

        $this->actingAs($admin)->put(route('budget-plans.realizations.update', [$budgetPlan, $expense]), [
            'expense_date' => now()->toDateString(),
            'vendor_id' => $vendor->id,
            'unit_price' => 300000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $newInvoice,
            'bank_mutation_file' => $newMutation,
        ])->assertRedirect();

        $expense->refresh();

        Storage::assertMissing($oldInvoicePath);
        Storage::assertMissing($oldMutationPath);
        Storage::assertExists($expense->invoice_proof_path);
        Storage::assertExists($expense->bank_mutation_path);

        $this->assertSame('new_nota.png', $expense->invoice_proof_original_name);
        $this->assertSame('new_mutasi.jpg', $expense->bank_mutation_original_name);
    }

    public function test_authorized_user_can_download_attachment_files(): void
    {
        Storage::fake('local');

        [, $admin, $project, , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $vendor = ProjectVendor::create(['project_id' => $project->id, 'name' => 'Vendor DL']);
        $invoiceFile = UploadedFile::fake()->create('nota.pdf', 50, 'application/pdf');

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_id' => $vendor->id,
            'unit_price' => 200000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $invoiceFile,
        ])->assertRedirect();

        $expense = ProjectExpense::where('budget_plan_id', $budgetPlan->id)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('budget-plans.realizations.invoice-proof', [$budgetPlan, $expense]))
            ->assertOk();
    }

    public function test_unauthorized_user_cannot_download_attachment(): void
    {
        Storage::fake('local');

        [, $admin, $project, , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $vendor = ProjectVendor::create(['project_id' => $project->id, 'name' => 'Vendor DL2']);
        $invoiceFile = UploadedFile::fake()->create('nota.pdf', 50, 'application/pdf');

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_id' => $vendor->id,
            'unit_price' => 200000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $invoiceFile,
        ])->assertRedirect();

        $expense = ProjectExpense::where('budget_plan_id', $budgetPlan->id)->firstOrFail();

        $otherCompany = Company::create(['name' => 'Other', 'type' => 'company']);
        $otherAdmin = User::factory()->create(['company_id' => $otherCompany->id, 'role' => 'admin_company']);

        $this->actingAs($otherAdmin)
            ->get(route('budget-plans.realizations.invoice-proof', [$budgetPlan, $expense]))
            ->assertForbidden();
    }

    public function test_attachment_file_with_invalid_format_is_rejected(): void
    {
        Storage::fake('local');

        [, $admin, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $invalidFile = UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Bad',
            'unit_price' => 100000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['invoice_proof_file']);
        $this->assertDatabaseEmpty('project_expenses');
    }

    public function test_attachment_file_exceeding_5mb_is_rejected(): void
    {
        Storage::fake('local');

        [, $admin, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $largeFile = UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf'); // 6MB

        $response = $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Big',
            'unit_price' => 100000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $largeFile,
        ]);

        $response->assertSessionHasErrors(['invoice_proof_file']);
        $this->assertDatabaseEmpty('project_expenses');
    }

    public function test_destroying_realization_deletes_attachment_files(): void
    {
        Storage::fake('local');

        [, $admin, , , $budgetPlan, $item] = $this->makeApprovedBudgetPlanContext();

        $invoiceFile = UploadedFile::fake()->create('nota.pdf', 50, 'application/pdf');

        $this->actingAs($admin)->post(route('budget-plans.realizations.store', $budgetPlan), [
            'budget_plan_item_id' => $item->id,
            'expense_date' => now()->toDateString(),
            'vendor_new_name' => 'Vendor Del',
            'unit_price' => 200000,
            'quantity' => 1,
            'unit' => 'unit',
            'invoice_proof_file' => $invoiceFile,
        ])->assertRedirect();

        $expense = ProjectExpense::where('budget_plan_id', $budgetPlan->id)->firstOrFail();
        $path = $expense->invoice_proof_path;

        Storage::assertExists($path);

        $this->actingAs($admin)
            ->delete(route('budget-plans.realizations.destroy', [$budgetPlan, $expense]))
            ->assertRedirect();

        Storage::assertMissing($path);
        $this->assertDatabaseMissing('project_expenses', ['id' => $expense->id]);
    }

    private function makeApprovedBudgetPlanContext(): array
    {
        $company = Company::create([
            'name' => 'Company BP',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project BP',
            'address' => 'Alamat',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $chartAccount = ChartAccount::create([
            'company_id' => $company->id,
            'code' => '6100',
            'name' => 'Akun BP',
            'is_active' => true,
        ]);

        $budgetPlan = BudgetPlan::create([
            'bp_number' => 'BP-TEST-REAL-00001',
            'company_id' => $company->id,
            'requester_id' => $admin->id,
            'status' => BudgetPlan::STATUS_APPROVED,
            'approved_at' => now(),
            'total_amount' => 1000000,
            'submission_date' => now()->toDateString(),
            'week_of_month' => 1,
            'project_count' => 1,
            'category' => 'Operasional',
        ]);

        $item = BudgetPlanItem::create([
            'budget_plan_id' => $budgetPlan->id,
            'project_id' => $project->id,
            'bank_account_id' => null,
            'chart_account_id' => $chartAccount->id,
            'item_name' => 'Item BP Realisasi',
            'vendor_name' => null,
            'category' => 'Operasional',
            'unit_price' => 1000000,
            'quantity' => 1,
            'unit' => 'unit',
            'line_total' => 1000000,
            'real_amount' => 777777,
        ]);

        return [$company, $admin, $project, $chartAccount, $budgetPlan, $item];
    }
}
