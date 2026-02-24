<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use App\Models\Project;
use App\Models\TaxMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()
            ->when($request->user()->isAdminCompany(), function ($query) use ($request) {
                $query->where('company_id', $request->user()->company_id);
            })
            ->when($request->user()->isFinanceHolding(), function ($query) use ($request) {
                $query->whereHas('company', function ($companyQuery) use ($request) {
                    $companyQuery->where('parent_id', $request->user()->company_id);
                });
            })
            ->with(['pphTaxMaster', 'ppnTaxMaster'])
            ->latest()
            ->get();

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        [$pphTaxMasters, $ppnTaxMasters] = $this->activeTaxMasters();

        return view('projects.create', [
            'pphTaxMasters' => $pphTaxMasters,
            'ppnTaxMasters' => $ppnTaxMasters,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $data = $this->validatePayload($request);
        $projectPayload = $this->buildProjectPayload($data, $request->user()->company_id);

        Project::create($projectPayload);

        return redirect()->route('projects.index');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->loadMissing([
            'company',
            'pphTaxMaster',
            'ppnTaxMaster',
            'members' => fn ($query) => $query->orderBy('name'),
            'terms' => fn ($query) => $query->orderBy('sequence_no'),
            'vendors' => fn ($query) => $query->orderBy('name'),
            'expenses' => fn ($query) => $query
                ->with(['vendor', 'chartAccount', 'budgetPlan'])
                ->latest('expense_date')
                ->latest('id'),
            'progresses' => fn ($query) => $query
                ->with('creator')
                ->orderBy('progress_date')
                ->orderBy('id'),
            'receipts' => fn ($query) => $query
                ->with(['allocations.term', 'approver'])
                ->latest('receipt_date')
                ->latest('id'),
        ]);

        $chartAccounts = ChartAccount::query()
            ->where('company_id', $project->company_id)
            ->where('is_active', true)
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        $progressHistory = $project->progresses
            ->values()
            ->map(function ($progress, int $index) use ($project) {
                $previous = $index > 0 ? $project->progresses[$index - 1] : null;
                $delta = $previous
                    ? round((float) $progress->progress_percent - (float) $previous->progress_percent, 2)
                    : null;

                return (object) [
                    'progress' => $progress,
                    'previous_percent' => $previous ? (float) $previous->progress_percent : null,
                    'delta_percent' => $delta,
                ];
            })
            ->reverse()
            ->values();

        $memberIds    = $project->members->pluck('id')->toArray();
        $addableUsers = User::where('company_id', $project->company_id)
            ->whereNotIn('id', $memberIds)
            ->orderBy('name')
            ->get();

        return view('projects.show', [
            'project'         => $project,
            'chartAccounts'   => $chartAccounts,
            'progressHistory' => $progressHistory,
            'addableUsers'    => $addableUsers,
        ]);
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        [$pphTaxMasters, $ppnTaxMasters] = $this->activeTaxMasters();

        return view('projects.edit', [
            'project' => $project,
            'pphTaxMasters' => $pphTaxMasters,
            'ppnTaxMasters' => $ppnTaxMasters,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $this->validatePayload($request);
        $projectPayload = $this->buildProjectPayload($data, $project->company_id);
        $project->update($projectPayload);

        return redirect()->route('projects.show', $project);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'start_work_date' => ['nullable', 'date'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'use_pph' => ['required', 'boolean'],
            'pph_tax_master_id' => [
                Rule::requiredIf($request->boolean('use_pph')),
                'nullable',
                Rule::exists('tax_masters', 'id')->where(function ($query) {
                    $query->where('tax_type', TaxMaster::TYPE_PPH)->where('is_active', true);
                }),
            ],
            'use_ppn' => ['required', 'boolean'],
            'ppn_tax_master_id' => [
                Rule::requiredIf($request->boolean('use_ppn')),
                'nullable',
                Rule::exists('tax_masters', 'id')->where(function ($query) {
                    $query->where('tax_type', TaxMaster::TYPE_PPN)->where('is_active', true);
                }),
            ],
        ]);
    }

    private function buildProjectPayload(array $data, int $companyId): array
    {
        $usePph = (bool) $data['use_pph'];
        $usePpn = (bool) $data['use_ppn'];

        $pphMaster = null;
        if ($usePph) {
            $pphMaster = TaxMaster::query()
                ->active()
                ->where('tax_type', TaxMaster::TYPE_PPH)
                ->findOrFail($data['pph_tax_master_id']);
        }

        $ppnMaster = null;
        if ($usePpn) {
            $ppnMaster = TaxMaster::query()
                ->active()
                ->where('tax_type', TaxMaster::TYPE_PPN)
                ->findOrFail($data['ppn_tax_master_id']);
        }

        return [
            'company_id' => $companyId,
            'name' => $data['name'],
            'address' => $data['address'],
            'start_work_date' => $data['start_work_date'] ?? null,
            'contract_value' => $data['contract_value'],
            'use_pph' => $usePph,
            'pph_tax_master_id' => $usePph ? $pphMaster?->id : null,
            'pph_rate' => $usePph ? $pphMaster?->percentage : null,
            'use_ppn' => $usePpn,
            'ppn_tax_master_id' => $usePpn ? $ppnMaster?->id : null,
            'ppn_rate' => $usePpn ? $ppnMaster?->percentage : null,
        ];
    }

    private function activeTaxMasters(): array
    {
        $taxMasters = TaxMaster::query()
            ->active()
            ->orderBy('tax_type')
            ->orderBy('name')
            ->get();

        return [
            $taxMasters->where('tax_type', TaxMaster::TYPE_PPH)->values(),
            $taxMasters->where('tax_type', TaxMaster::TYPE_PPN)->values(),
        ];
    }
}
