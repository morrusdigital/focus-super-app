<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectReceipt;
use App\Models\ProjectTerm;
use App\Services\ProjectReceiptService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectReceiptController extends Controller
{
    public function __construct(private readonly ProjectReceiptService $service)
    {
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $receipts = $project->receipts()
            ->with(['allocations.term', 'approver'])
            ->latest('receipt_date')
            ->latest('id')
            ->get();

        if (request()->expectsJson()) {
            return response()->json($receipts);
        }

        return redirect()->route('projects.show', $project);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('manageReceipts', $project);

        if ($project->contract_value === null) {
            throw ValidationException::withMessages([
                'amount' => 'Project tanpa nilai kontrak tidak dapat menerima pencatatan dana masuk.',
            ]);
        }

        $data = $request->validate([
            'project_term_id' => [
                'required',
                'integer',
                Rule::exists('project_terms', 'id')->where(function ($query) use ($project) {
                    $query->where('project_id', $project->id)
                        ->where('status', ProjectTerm::STATUS_SENT);
                }),
            ],
            'receipt_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'source' => ['nullable', 'string', 'max:100'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $selectedTerm = $project->terms()
            ->where('id', $data['project_term_id'])
            ->where('status', ProjectTerm::STATUS_SENT)
            ->first();

        if (!$selectedTerm || $selectedTerm->outstanding_amount <= 0) {
            throw ValidationException::withMessages([
                'project_term_id' => 'Invoice yang dipilih tidak valid atau sudah lunas.',
            ]);
        }

        $this->service->createReceipt($project, $data);

        return redirect()->route('projects.show', $project)->with('status', 'Dana masuk berhasil dicatat.');
    }

    public function destroy(Project $project, ProjectReceipt $receipt)
    {
        $this->authorize('manageReceipts', $project);

        if ((int) $receipt->project_id !== (int) $project->id) {
            abort(404);
        }

        $this->service->deleteReceipt($receipt);

        return redirect()->route('projects.show', $project)->with('status', 'Dana masuk berhasil dihapus.');
    }

}
