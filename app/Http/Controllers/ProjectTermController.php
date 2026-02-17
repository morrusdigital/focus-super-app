<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTerm;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectTermController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $terms = $project->terms()
            ->orderBy('sequence_no')
            ->get();

        if (request()->expectsJson()) {
            return response()->json($terms);
        }

        return redirect()->route('projects.show', $project);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('manageTerms', $project);

        $data = $this->validatePayload($request);
        $sequenceNo = $this->nextSequenceNumber($project);
        $amount = $this->resolveAmount($project, $data);
        $this->assertTotalTermLimit($project, $amount);

        $project->terms()->create([
            'sequence_no' => $sequenceNo,
            'name' => 'Termin ' . $sequenceNo,
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => $data['percentage'],
            'amount' => $amount,
            'status' => ProjectTerm::STATUS_DRAFT,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Termin berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectTerm $term)
    {
        $this->authorize('manageTerms', $project);
        $this->assertProjectTermBelongsToProject($project, $term);

        if ($term->status === ProjectTerm::STATUS_PAID) {
            throw ValidationException::withMessages([
                'term' => 'Termin yang sudah lunas tidak bisa diubah.',
            ]);
        }

        $data = $this->validatePayload($request);
        $amount = $this->resolveAmount($project, $data);
        $this->assertTotalTermLimit($project, $amount, $term);

        $term->update([
            'sequence_no' => $term->sequence_no,
            'name' => 'Termin ' . $term->sequence_no,
            'basis_type' => ProjectTerm::BASIS_PERCENTAGE,
            'percentage' => $data['percentage'],
            'amount' => $amount,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Termin berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectTerm $term)
    {
        $this->authorize('manageTerms', $project);
        $this->assertProjectTermBelongsToProject($project, $term);

        if ($term->status === ProjectTerm::STATUS_PAID) {
            throw ValidationException::withMessages([
                'term' => 'Termin yang sudah lunas tidak bisa dihapus.',
            ]);
        }

        if ($term->allocations()->exists()) {
            throw ValidationException::withMessages([
                'term' => 'Termin sudah memiliki transaksi penerimaan dan tidak bisa dihapus.',
            ]);
        }

        $term->delete();

        return redirect()->route('projects.show', $project)->with('status', 'Termin berhasil dihapus.');
    }

    public function markSent(Project $project, ProjectTerm $term)
    {
        $this->authorize('manageTerms', $project);
        $this->assertProjectTermBelongsToProject($project, $term);

        if ($term->status !== ProjectTerm::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'term' => 'Hanya termin draft yang bisa dikirim invoice.',
            ]);
        }

        $prefix = 'INV-' . $project->id . '-' . now()->format('Ym') . '-';
        $lastNumber = ProjectTerm::query()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = 1;
        if ($lastNumber) {
            $sequence = (int) substr($lastNumber, -3) + 1;
        }

        $invoiceNumber = $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        $term->update([
            'status' => ProjectTerm::STATUS_SENT,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->toDateString(),
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Invoice termin berhasil dikirim.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'percentage' => ['required', 'numeric', 'gt:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function resolveAmount(Project $project, array $data): float
    {
        $contractBase = $project->contract_value_with_ppn;
        if ($contractBase === null || $contractBase <= 0) {
            throw ValidationException::withMessages([
                'percentage' => 'Nilai Kontrak SPK + PPN project belum tersedia untuk menghitung termin persentase.',
            ]);
        }

        return round($contractBase * ((float) $data['percentage'] / 100), 2);
    }

    private function assertTotalTermLimit(Project $project, float $incomingAmount, ?ProjectTerm $except = null): void
    {
        $contractBase = $project->contract_value_with_ppn;
        if ($contractBase === null) {
            throw ValidationException::withMessages([
                'amount' => 'Nilai Kontrak SPK + PPN project belum tersedia.',
            ]);
        }

        $existing = (float) $project->terms()
            ->when($except, fn ($query) => $query->where('id', '!=', $except->id))
            ->sum('amount');

        if (round($existing + $incomingAmount, 2) > round((float) $contractBase, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'Total nilai termin tidak boleh melebihi Nilai Kontrak SPK + PPN.',
            ]);
        }
    }

    private function assertProjectTermBelongsToProject(Project $project, ProjectTerm $term): void
    {
        if ((int) $project->id !== (int) $term->project_id) {
            abort(404);
        }
    }

    private function nextSequenceNumber(Project $project): int
    {
        $lastSequence = (int) $project->terms()->max('sequence_no');

        return $lastSequence + 1;
    }
}
