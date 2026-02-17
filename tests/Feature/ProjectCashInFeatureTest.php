<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectReceipt;
use App\Models\ProjectTerm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCashInFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_create_percentage_terms_only(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Termin',
            'address' => 'Alamat',
            'contract_value' => 100000000,
            'use_pph' => true,
            'pph_rate' => 10,
            'use_ppn' => true,
            'ppn_rate' => 11,
        ]);

        $this->actingAs($admin)->post(route('projects.terms.store', $project), [
            'percentage' => 30,
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_terms', [
            'project_id' => $project->id,
            'sequence_no' => 1,
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 33300000,
            'status' => ProjectTerm::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)->post(route('projects.terms.store', $project), [
            'percentage' => 50,
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_terms', [
            'project_id' => $project->id,
            'sequence_no' => 2,
            'amount' => 55500000,
            'status' => ProjectTerm::STATUS_DRAFT,
        ]);
    }

    public function test_mark_sent_generates_invoice_number(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();
        $project = $this->makeConfiguredProject($company);

        $term = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 1,
            'name' => 'Termin 1',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 20,
            'amount' => 20000000,
            'status' => ProjectTerm::STATUS_DRAFT,
        ]);

        $this->actingAs($admin)->post(route('project-terms.mark-sent', [$project, $term]))
            ->assertRedirect(route('projects.show', $project));

        $term->refresh();
        $this->assertSame(ProjectTerm::STATUS_SENT, $term->status);
        $this->assertMatchesRegularExpression('/^INV-' . $project->id . '-' . now()->format('Ym') . '-\d{3}$/', (string) $term->invoice_number);
    }

    public function test_receipt_allocation_sequential_and_overpayment_becomes_unapplied_balance(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();
        $project = $this->makeConfiguredProject($company);

        $term1 = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 1,
            'name' => 'Termin 1',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 30000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);
        ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 2,
            'name' => 'Termin 2',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 30000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);

        $this->actingAs($admin)->post(route('projects.receipts.store', $project), [
            'project_term_id' => $term1->id,
            'receipt_date' => now()->format('Y-m-d'),
            'amount' => 70000000,
        ])->assertRedirect(route('projects.show', $project));

        $receipt = ProjectReceipt::firstOrFail();
        $this->assertSame(ProjectReceipt::APPROVAL_NOT_REQUIRED, $receipt->approval_status);
        $this->assertFalse($receipt->is_partial);

        $this->assertDatabaseCount('project_receipt_allocations', 2);
        $project->refresh();
        $this->assertSame('10000000.00', (string) $project->unapplied_balance);
        $this->assertDatabaseHas('project_terms', [
            'project_id' => $project->id,
            'sequence_no' => 1,
            'status' => ProjectTerm::STATUS_PAID,
        ]);
        $this->assertDatabaseHas('project_terms', [
            'project_id' => $project->id,
            'sequence_no' => 2,
            'status' => ProjectTerm::STATUS_PAID,
        ]);
    }

    public function test_receipt_can_start_allocation_from_selected_invoice(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();
        $project = $this->makeConfiguredProject($company);

        $term1 = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 1,
            'name' => 'Termin 1',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 30000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);
        $term2 = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 2,
            'name' => 'Termin 2',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 30000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);

        $this->actingAs($admin)->post(route('projects.receipts.store', $project), [
            'project_term_id' => $term2->id,
            'receipt_date' => now()->format('Y-m-d'),
            'amount' => 30000000,
        ])->assertRedirect(route('projects.show', $project));

        $receipt = ProjectReceipt::firstOrFail();

        $this->assertDatabaseHas('project_receipt_allocations', [
            'project_receipt_id' => $receipt->id,
            'project_term_id' => $term2->id,
            'amount' => 30000000,
        ]);
        $this->assertDatabaseMissing('project_receipt_allocations', [
            'project_receipt_id' => $receipt->id,
            'project_term_id' => $term1->id,
        ]);

        $this->assertDatabaseHas('project_terms', [
            'id' => $term1->id,
            'status' => ProjectTerm::STATUS_SENT,
        ]);
        $this->assertDatabaseHas('project_terms', [
            'id' => $term2->id,
            'status' => ProjectTerm::STATUS_PAID,
        ]);
    }

    public function test_partial_receipt_is_applied_without_approval_flow(): void
    {
        $holding = Company::create([
            'name' => 'Holding',
            'type' => 'holding',
        ]);
        $company = Company::create([
            'name' => 'Company Child',
            'type' => 'company',
            'parent_id' => $holding->id,
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = $this->makeConfiguredProject($company);

        $term1 = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 1,
            'name' => 'Termin 1',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 30000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);
        ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 2,
            'name' => 'Termin 2',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 30000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);

        $this->actingAs($admin)->post(route('projects.receipts.store', $project), [
            'project_term_id' => $term1->id,
            'receipt_date' => now()->format('Y-m-d'),
            'amount' => 35000000,
        ])->assertRedirect(route('projects.show', $project));

        $receipt = ProjectReceipt::firstOrFail();
        $this->assertTrue($receipt->is_partial);
        $this->assertSame(ProjectReceipt::APPROVAL_NOT_REQUIRED, $receipt->approval_status);

        $this->assertDatabaseHas('project_terms', [
            'project_id' => $project->id,
            'sequence_no' => 1,
            'status' => ProjectTerm::STATUS_PAID,
        ]);
        $this->assertDatabaseHas('project_terms', [
            'project_id' => $project->id,
            'sequence_no' => 2,
            'status' => ProjectTerm::STATUS_SENT,
        ]);
    }

    public function test_finance_holding_can_input_receipt_for_child_company_project(): void
    {
        $holding = Company::create([
            'name' => 'Holding 3',
            'type' => 'holding',
        ]);
        $company = Company::create([
            'name' => 'Company Child 3',
            'type' => 'company',
            'parent_id' => $holding->id,
        ]);

        $finance = User::factory()->create([
            'company_id' => $holding->id,
            'role' => 'finance_holding',
        ]);

        $project = $this->makeConfiguredProject($company);
        $term = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 1,
            'name' => 'Termin 1',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 20,
            'amount' => 20000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);

        $response = $this->actingAs($finance)->post(route('projects.receipts.store', $project), [
            'project_term_id' => $term->id,
            'receipt_date' => now()->format('Y-m-d'),
            'amount' => 5000000,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('project_receipts', [
            'project_id' => $project->id,
            'amount' => 5000000,
        ]);
    }

    public function test_admin_company_can_delete_receipt(): void
    {
        [$company, $admin] = $this->makeCompanyAdmin();
        $project = $this->makeConfiguredProject($company);

        $term = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 1,
            'name' => 'Termin 1',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 30,
            'amount' => 30000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);

        $this->actingAs($admin)->post(route('projects.receipts.store', $project), [
            'project_term_id' => $term->id,
            'receipt_date' => now()->format('Y-m-d'),
            'amount' => 10000000,
        ])->assertRedirect(route('projects.show', $project));

        $receipt = ProjectReceipt::firstOrFail();

        $this->actingAs($admin)->delete(route('projects.receipts.destroy', [$project, $receipt]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_receipts', [
            'id' => $receipt->id,
        ]);
        $this->assertDatabaseCount('project_receipt_allocations', 0);
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

    private function makeConfiguredProject(Company $company): Project
    {
        return Project::create([
            'company_id' => $company->id,
            'name' => 'Project Cash In',
            'address' => 'Alamat Project',
            'contract_value' => 100000000,
            'use_pph' => true,
            'pph_rate' => 10,
            'use_ppn' => false,
            'unapplied_balance' => 0,
        ]);
    }
}
