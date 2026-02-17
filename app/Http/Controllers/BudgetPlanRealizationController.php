<?php

namespace App\Http\Controllers;

use App\Models\BudgetPlan;
use App\Models\BudgetPlanItem;
use App\Models\ProjectExpense;
use App\Models\ProjectVendor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BudgetPlanRealizationController extends Controller
{
    public function index(BudgetPlan $budgetPlan)
    {
        $this->authorize('view', $budgetPlan);

        $realizations = ProjectExpense::query()
            ->with(['project', 'chartAccount', 'vendor', 'budgetPlanItem'])
            ->where('budget_plan_id', $budgetPlan->id)
            ->where('expense_source', ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION)
            ->latest('expense_date')
            ->latest('id')
            ->get();

        if (request()->expectsJson()) {
            return response()->json($realizations);
        }

        return redirect()->route('budget-plans.show', $budgetPlan);
    }

    public function store(Request $request, BudgetPlan $budgetPlan)
    {
        $this->authorize('manageRealization', $budgetPlan);

        $data = $this->validatePayload($request);
        $item = $this->resolveBudgetPlanItem($budgetPlan, (int) $data['budget_plan_item_id']);
        $this->assertItemCanBeRealized($item);
        $vendor = $this->resolveVendor(
            (int) $item->project_id,
            isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            $data['vendor_new_name'] ?? null
        );

        $amount = round((float) $data['unit_price'] * (float) $data['quantity'], 2);

        ProjectExpense::create([
            'project_id' => (int) $item->project_id,
            'budget_plan_id' => $budgetPlan->id,
            'budget_plan_item_id' => $item->id,
            'expense_source' => ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION,
            'vendor_id' => $vendor->id,
            'chart_account_id' => (int) $item->chart_account_id,
            'expense_date' => $data['expense_date'],
            'item_name' => $item->item_name,
            'unit_price' => $data['unit_price'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'amount' => $amount,
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('budget-plans.show', $budgetPlan)
            ->with('status', 'Realisasi budget plan berhasil ditambahkan.');
    }

    public function update(Request $request, BudgetPlan $budgetPlan, ProjectExpense $expense)
    {
        $this->authorize('manageRealization', $budgetPlan);
        $this->assertExpenseBelongsToBudgetPlan($budgetPlan, $expense);

        $data = $this->validatePayload($request, false);
        $vendor = $this->resolveVendor(
            (int) $expense->project_id,
            isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            $data['vendor_new_name'] ?? null
        );

        $amount = round((float) $data['unit_price'] * (float) $data['quantity'], 2);

        $expense->update([
            'vendor_id' => $vendor->id,
            'expense_date' => $data['expense_date'],
            'unit_price' => $data['unit_price'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'amount' => $amount,
            'notes' => $data['notes'] ?? null,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('budget-plans.show', $budgetPlan)
            ->with('status', 'Realisasi budget plan berhasil diperbarui.');
    }

    public function destroy(BudgetPlan $budgetPlan, ProjectExpense $expense)
    {
        $this->authorize('manageRealization', $budgetPlan);
        $this->assertExpenseBelongsToBudgetPlan($budgetPlan, $expense);

        $expense->delete();

        return redirect()->route('budget-plans.show', $budgetPlan)
            ->with('status', 'Realisasi budget plan berhasil dihapus.');
    }

    private function validatePayload(Request $request, bool $withItem = true): array
    {
        $rules = [
            'expense_date' => ['required', 'date'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'vendor_id' => ['nullable', 'integer'],
            'vendor_new_name' => ['nullable', 'string', 'max:255'],
        ];

        if ($withItem) {
            $rules['budget_plan_item_id'] = ['required', 'integer'];
        }

        $data = $request->validate($rules);

        $hasVendorId = isset($data['vendor_id']) && (int) $data['vendor_id'] > 0;
        $hasVendorName = filled($data['vendor_new_name'] ?? null);

        if (! $hasVendorId && ! $hasVendorName) {
            throw ValidationException::withMessages([
                'vendor_id' => 'Pilih vendor existing atau isi vendor baru.',
            ]);
        }

        return $data;
    }

    private function resolveBudgetPlanItem(BudgetPlan $budgetPlan, int $itemId): BudgetPlanItem
    {
        $item = BudgetPlanItem::query()
            ->where('budget_plan_id', $budgetPlan->id)
            ->find($itemId);

        if (! $item) {
            throw ValidationException::withMessages([
                'budget_plan_item_id' => 'Item budget plan tidak valid.',
            ]);
        }

        return $item;
    }

    private function assertItemCanBeRealized(BudgetPlanItem $item): void
    {
        if (! $item->project_id) {
            throw ValidationException::withMessages([
                'budget_plan_item_id' => 'Item BP tidak memiliki project.',
            ]);
        }

        if (! $item->chart_account_id) {
            throw ValidationException::withMessages([
                'budget_plan_item_id' => 'Item BP tidak memiliki akun.',
            ]);
        }
    }

    private function resolveVendor(int $projectId, ?int $vendorId, ?string $vendorNewName): ProjectVendor
    {
        $vendorName = trim((string) $vendorNewName);

        if ($vendorName !== '') {
            $candidate = ProjectVendor::query()
                ->where('project_id', $projectId)
                ->get()
                ->first(function (ProjectVendor $vendor) use ($vendorName) {
                    return mb_strtolower(trim($vendor->name)) === mb_strtolower($vendorName);
                });

            if ($candidate) {
                return $candidate;
            }

            return ProjectVendor::create([
                'project_id' => $projectId,
                'name' => $vendorName,
            ]);
        }

        $vendor = $vendorId
            ? ProjectVendor::query()
                ->where('project_id', $projectId)
                ->find($vendorId)
            : null;

        if (! $vendor) {
            throw ValidationException::withMessages([
                'vendor_id' => 'Vendor tidak valid untuk project item BP yang dipilih.',
            ]);
        }

        return $vendor;
    }

    private function assertExpenseBelongsToBudgetPlan(BudgetPlan $budgetPlan, ProjectExpense $expense): void
    {
        if ((int) $expense->budget_plan_id !== (int) $budgetPlan->id ||
            $expense->expense_source !== ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION) {
            abort(404);
        }
    }
}
