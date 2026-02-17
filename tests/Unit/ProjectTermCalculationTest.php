<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectReceipt;
use App\Models\ProjectReceiptAllocation;
use App\Models\ProjectTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTermCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_outstanding_amount_excludes_only_rejected_receipts(): void
    {
        $company = Company::create([
            'name' => 'Company',
            'type' => 'company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project',
            'address' => 'Alamat',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $term = ProjectTerm::create([
            'project_id' => $project->id,
            'sequence_no' => 1,
            'name' => 'Termin 1',
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => 10,
            'amount' => 10000000,
            'status' => ProjectTerm::STATUS_SENT,
        ]);

        $receiptEffective = ProjectReceipt::create([
            'project_id' => $project->id,
            'receipt_date' => now()->toDateString(),
            'amount' => 6000000,
            'approval_status' => ProjectReceipt::APPROVAL_NOT_REQUIRED,
        ]);
        ProjectReceiptAllocation::create([
            'project_receipt_id' => $receiptEffective->id,
            'project_term_id' => $term->id,
            'amount' => 6000000,
        ]);

        $receiptPending = ProjectReceipt::create([
            'project_id' => $project->id,
            'receipt_date' => now()->toDateString(),
            'amount' => 2000000,
            'approval_status' => ProjectReceipt::APPROVAL_PENDING,
        ]);
        ProjectReceiptAllocation::create([
            'project_receipt_id' => $receiptPending->id,
            'project_term_id' => $term->id,
            'amount' => 2000000,
        ]);

        $receiptRejected = ProjectReceipt::create([
            'project_id' => $project->id,
            'receipt_date' => now()->toDateString(),
            'amount' => 1500000,
            'approval_status' => ProjectReceipt::APPROVAL_REJECTED,
        ]);
        ProjectReceiptAllocation::create([
            'project_receipt_id' => $receiptRejected->id,
            'project_term_id' => $term->id,
            'amount' => 1500000,
        ]);

        $term->refresh();
        $this->assertSame(2000000.0, $term->outstanding_amount);
    }
}
