<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetPlan\ReviewBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\StoreBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\SubmitBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\UpdateBudgetPlanRequest;
use App\Models\BudgetPlan;
use App\Services\BudgetPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetPlanController extends Controller
{
    public function __construct(private readonly BudgetPlanService $service)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = BudgetPlan::with(['company', 'requester'])->latest();

        $status = $request->query('status');
        if ($status && in_array($status, BudgetPlan::STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($user->isAdminCompany()) {
            $query->where('company_id', $user->company_id);
        } else {
            $holdingId = $this->getHoldingId($user);
            if (! $holdingId) {
                abort(403);
            }

            $companyIds = DB::table('companies')
                ->where('parent_id', $holdingId)
                ->pluck('id');

            if ($companyIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('company_id', $companyIds);
            }
        }

        return view('budget_plans.index', [
            'budgetPlans' => $query->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', BudgetPlan::class);

        return view('budget_plans.create');
    }

    public function store(StoreBudgetPlanRequest $request)
    {
        $user = $request->user();
        $data = [
            'company_id' => $user->company_id,
            'requester_id' => $user->id,
            'status' => BudgetPlan::STATUS_DRAFT,
            'tanggal' => $request->input('tanggal'),
            'notes' => $request->input('notes'),
        ];

        $budgetPlan = $this->service->create($data, $request->input('items', []), $user->id);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function show(BudgetPlan $budgetPlan)
    {
        $this->authorize('view', $budgetPlan);

        return view('budget_plans.show', [
            'budgetPlan' => $budgetPlan->load(['items', 'logs.actor', 'company', 'requester']),
        ]);
    }

    public function edit(BudgetPlan $budgetPlan)
    {
        $this->authorize('update', $budgetPlan);

        return view('budget_plans.edit', [
            'budgetPlan' => $budgetPlan->load('items'),
        ]);
    }

    public function update(UpdateBudgetPlanRequest $request, BudgetPlan $budgetPlan)
    {
        $user = $request->user();
        $data = [
            'tanggal' => $request->input('tanggal'),
            'notes' => $request->input('notes'),
        ];

        $budgetPlan = $this->service->update($budgetPlan, $data, $request->input('items', []), $user->id);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function destroy(Request $request, BudgetPlan $budgetPlan)
    {
        $this->authorize('delete', $budgetPlan);

        $budgetPlan->delete();

        return redirect()->route('budget-plans.index');
    }

    public function submit(SubmitBudgetPlanRequest $request, BudgetPlan $budgetPlan)
    {
        $budgetPlan->update([
            'status' => BudgetPlan::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $budgetPlan->logs()->create([
            'actor_id' => $request->user()->id,
            'action' => 'submitted',
        ]);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function approve(ReviewBudgetPlanRequest $request, BudgetPlan $budgetPlan)
    {
        $budgetPlan->update([
            'status' => BudgetPlan::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $budgetPlan->logs()->create([
            'actor_id' => $request->user()->id,
            'action' => 'approved',
            'note' => $request->input('note'),
        ]);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function reject(ReviewBudgetPlanRequest $request, BudgetPlan $budgetPlan)
    {
        $budgetPlan->update([
            'status' => BudgetPlan::STATUS_REJECTED,
            'rejected_at' => now(),
        ]);

        $budgetPlan->logs()->create([
            'actor_id' => $request->user()->id,
            'action' => 'rejected',
            'note' => $request->input('note'),
        ]);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function requestRevision(ReviewBudgetPlanRequest $request, BudgetPlan $budgetPlan)
    {
        $budgetPlan->update([
            'status' => BudgetPlan::STATUS_REVISION_REQUESTED,
            'revision_requested_at' => now(),
        ]);

        $budgetPlan->logs()->create([
            'actor_id' => $request->user()->id,
            'action' => 'revision_requested',
            'note' => $request->input('note'),
        ]);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    private function getHoldingId($user): ?int
    {
        if (! $user->isFinanceHolding() || ! $user->company_id) {
            return null;
        }

        $type = DB::table('companies')->where('id', $user->company_id)->value('type');
        if ($type !== 'holding') {
            return null;
        }

        return (int) $user->company_id;
    }
}
