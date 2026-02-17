<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetPlan\ReviewBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\RecordRealExpenseRequest;
use App\Http\Requests\BudgetPlan\StoreBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\SubmitBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\UpdateBudgetPlanRequest;
use App\Models\BudgetPlan;
use App\Models\BudgetPlanItem;
use App\Models\BudgetPlanCategory;
use App\Models\ChartAccount;
use App\Models\CompanyBankAccount;
use App\Models\Project;
use App\Services\BudgetPlanService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetPlanController extends Controller
{
    public function __construct(private readonly BudgetPlanService $service)
    {
    }

    public function index(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        return view('budget_plans.index', [
            'budgetPlans' => $query->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', BudgetPlan::class);

        $user = request()->user();
        $projects = Project::where('company_id', $user->company_id)->orderBy('name')->get();
        $bankAccounts = CompanyBankAccount::where('company_id', $user->company_id)->orderBy('bank_name')->get();
        $chartAccounts = ChartAccount::where('company_id', $user->company_id)
            ->orderBy('code')
            ->get();
        $categories = BudgetPlanCategory::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('budget_plans.create', [
            'projects' => $projects,
            'bankAccounts' => $bankAccounts,
            'chartAccounts' => $chartAccounts,
            'categories' => $categories,
        ]);
    }

    public function store(StoreBudgetPlanRequest $request)
    {
        $user = $request->user();
        $data = [
            'company_id' => $user->company_id,
            'requester_id' => $user->id,
            'status' => BudgetPlan::STATUS_DRAFT,
            'submission_date' => $request->input('submission_date'),
            'category' => $request->input('category'),
            'notes' => $request->input('notes'),
        ];

        $budgetPlan = $this->service->create($data, $request->input('items', []), $user->id);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function show(BudgetPlan $budgetPlan)
    {
        $this->authorize('view', $budgetPlan);

        $budgetPlan->load(['items.project', 'items.bankAccount', 'items.chartAccount', 'logs.actor', 'company', 'requester']);
        $company = $budgetPlan->company;

        $summary = null;
        if ($company) {
            $saldoAwal = (float) ($company->saldo_awal ?? 0);
            $totalRealExpense = (float) BudgetPlanItem::query()
                ->whereHas('budgetPlan', function ($query) use ($company) {
                    $query->where('company_id', $company->id)
                        ->where('status', BudgetPlan::STATUS_APPROVED);
                })
                ->sum('real_amount');

            $totalRequest = (float) BudgetPlan::query()
                ->where('company_id', $company->id)
                ->whereIn('status', [
                    BudgetPlan::STATUS_SUBMITTED,
                    BudgetPlan::STATUS_APPROVED,
                    BudgetPlan::STATUS_REVISION_REQUESTED,
                ])
                ->sum('total_amount');

            $saldoBerjalan = $saldoAwal - $totalRealExpense;
            $balanceDue = $totalRequest - $saldoBerjalan;

            $summary = [
                'saldo_awal' => $saldoAwal,
                'total_real_expense' => $totalRealExpense,
                'saldo_berjalan' => $saldoBerjalan,
                'total_request' => $totalRequest,
                'balance_due' => $balanceDue,
            ];
        }

        return view('budget_plans.show', [
            'budgetPlan' => $budgetPlan,
            'summary' => $summary,
            'categories' => BudgetPlanCategory::where('company_id', $budgetPlan->company_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(BudgetPlan $budgetPlan)
    {
        $this->authorize('update', $budgetPlan);

        $user = request()->user();
        $projects = Project::where('company_id', $user->company_id)->orderBy('name')->get();
        $bankAccounts = CompanyBankAccount::where('company_id', $user->company_id)->orderBy('bank_name')->get();
        $chartAccounts = ChartAccount::where('company_id', $user->company_id)
            ->orderBy('code')
            ->get();
        $categories = BudgetPlanCategory::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('budget_plans.edit', [
            'budgetPlan' => $budgetPlan->load('items'),
            'projects' => $projects,
            'bankAccounts' => $bankAccounts,
            'chartAccounts' => $chartAccounts,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateBudgetPlanRequest $request, BudgetPlan $budgetPlan)
    {
        $user = $request->user();
        $data = [
            'submission_date' => $request->input('submission_date'),
            'category' => $request->input('category'),
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

    public function recordExpense(RecordRealExpenseRequest $request, BudgetPlan $budgetPlan)
    {
        $items = $request->input('items', []);

        foreach ($items as $itemId => $itemData) {
            $budgetPlan->items()
                ->whereKey($itemId)
                ->update([
                    'real_amount' => $itemData['real_amount'] ?? 0,
                    'category' => $itemData['category'] ?? null,
                ]);
        }

        $budgetPlan->logs()->create([
            'actor_id' => $request->user()->id,
            'action' => 'real_expense_recorded',
        ]);

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function exportPdf(BudgetPlan $budgetPlan)
    {
        $this->authorize('view', $budgetPlan);

        $budgetPlan->load(['items.project', 'items.bankAccount', 'items.chartAccount', 'company', 'requester']);
        return $this->renderPdf(
            'budget_plans.pdf.show',
            ['budgetPlan' => $budgetPlan],
            'budget-plan-' . $budgetPlan->bp_number . '.pdf'
        );
    }

    public function exportPdfIndex(Request $request)
    {
        $budgetPlans = $this->buildIndexQuery($request)->get();
        return $this->renderPdf(
            'budget_plans.pdf.index',
            ['budgetPlans' => $budgetPlans],
            'budget-plans.pdf'
        );
    }

    private function buildIndexQuery(Request $request)
    {
        $this->authorize('viewAny', BudgetPlan::class);

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

        return $query;
    }

    private function renderPdf(string $view, array $data, string $filename)
    {
        $html = view($view, $data)->render();
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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
