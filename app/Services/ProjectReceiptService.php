<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectReceipt;
use App\Models\ProjectTerm;
use Illuminate\Support\Facades\DB;

class ProjectReceiptService
{
    public function createReceipt(Project $project, array $data): ProjectReceipt
    {
        return DB::transaction(function () use ($project, $data) {
            $remaining = (float) $data['amount'];
            $allocations = [];
            $isPartial = false;

            $sentTermsQuery = $project->terms()
                ->where('status', ProjectTerm::STATUS_SENT);

            if (!empty($data['project_term_id'])) {
                $selectedTerm = $project->terms()
                    ->where('id', $data['project_term_id'])
                    ->where('status', ProjectTerm::STATUS_SENT)
                    ->first();

                if ($selectedTerm) {
                    $sentTermsQuery->where('sequence_no', '>=', $selectedTerm->sequence_no);
                }
            }

            $sentTerms = $sentTermsQuery
                ->orderBy('sequence_no')
                ->get();

            foreach ($sentTerms as $term) {
                $outstanding = $term->outstanding_amount;
                if ($outstanding <= 0 || $remaining <= 0) {
                    continue;
                }

                $allocated = min($remaining, $outstanding);
                $allocations[] = [
                    'project_term_id' => $term->id,
                    'amount' => $allocated,
                ];

                $remaining = round($remaining - $allocated, 2);
                if ($allocated < $outstanding) {
                    $isPartial = true;
                    break;
                }
            }

            $receipt = ProjectReceipt::create([
                'project_id' => $project->id,
                'receipt_date' => $data['receipt_date'],
                'amount' => $data['amount'],
                'source' => $data['source'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_partial' => $isPartial,
                'approval_status' => ProjectReceipt::APPROVAL_NOT_REQUIRED,
            ]);

            if ($allocations) {
                $receipt->allocations()->createMany($allocations);
            }

            $this->recalculateProjectBalances($project->fresh());

            return $receipt->fresh(['allocations.term']);
        });
    }

    public function deleteReceipt(ProjectReceipt $receipt): void
    {
        DB::transaction(function () use ($receipt) {
            $project = $receipt->project()->firstOrFail();
            $receipt->delete();
            $this->recalculateProjectBalances($project->fresh());
        });
    }

    public function recalculateProjectBalances(Project $project): void
    {
        $terms = $project->terms()->get();
        foreach ($terms as $term) {
            if ($term->status === ProjectTerm::STATUS_DRAFT) {
                continue;
            }

            $effectiveAllocated = (float) $term->allocations()
                ->whereHas('receipt', function ($query) {
                    $query->where('approval_status', '!=', ProjectReceipt::APPROVAL_REJECTED);
                })
                ->sum('amount');

            $outstanding = (float) $term->amount - $effectiveAllocated;
            $newStatus = $outstanding <= 0 ? ProjectTerm::STATUS_PAID : ProjectTerm::STATUS_SENT;

            if ($term->status !== $newStatus) {
                $term->update(['status' => $newStatus]);
            }
        }

        $totalEffectiveReceived = (float) $project->receipts()
            ->where('approval_status', '!=', ProjectReceipt::APPROVAL_REJECTED)
            ->sum('amount');

        $totalEffectiveAllocated = (float) DB::table('project_receipt_allocations as pra')
            ->join('project_receipts as pr', 'pr.id', '=', 'pra.project_receipt_id')
            ->where('pr.project_id', $project->id)
            ->where('pr.approval_status', '!=', ProjectReceipt::APPROVAL_REJECTED)
            ->sum('pra.amount');

        $unapplied = max(0, round($totalEffectiveReceived - $totalEffectiveAllocated, 2));

        $project->update([
            'unapplied_balance' => $unapplied,
        ]);
    }
}
