<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectExpense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectExpenseController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $expenses = $project->expenses()
            ->with(['vendor', 'chartAccount'])
            ->latest('expense_date')
            ->latest('id')
            ->get();

        if (request()->expectsJson()) {
            return response()->json($expenses);
        }

        return redirect()->route('projects.show', $project);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('manageExpenses', $project);

        $data = $this->validatePayload($request, $project);
        $amount = round((float) $data['unit_price'] * (float) $data['quantity'], 2);

        $project->expenses()->create([
            'vendor_id' => (int) $data['vendor_id'],
            'expense_source' => ProjectExpense::SOURCE_MANUAL_PROJECT,
            'chart_account_id' => (int) $data['chart_account_id'],
            'expense_date' => $data['expense_date'],
            'item_name' => $data['item_name'],
            'unit_price' => $data['unit_price'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'amount' => $amount,
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Expense project berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectExpense $expense)
    {
        $this->authorize('manageExpenses', $project);
        $this->assertExpenseBelongsToProject($project, $expense);
        $this->assertExpenseCanBeManagedFromProject($expense);

        $data = $this->validatePayload($request, $project);
        $amount = round((float) $data['unit_price'] * (float) $data['quantity'], 2);

        $expense->update([
            'vendor_id' => (int) $data['vendor_id'],
            'chart_account_id' => (int) $data['chart_account_id'],
            'expense_date' => $data['expense_date'],
            'item_name' => $data['item_name'],
            'unit_price' => $data['unit_price'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'amount' => $amount,
            'notes' => $data['notes'] ?? null,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Expense project berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectExpense $expense)
    {
        $this->authorize('manageExpenses', $project);
        $this->assertExpenseBelongsToProject($project, $expense);
        $this->assertExpenseCanBeManagedFromProject($expense);

        $expense->delete();

        return redirect()->route('projects.show', $project)->with('status', 'Expense project berhasil dihapus.');
    }

    private function validatePayload(Request $request, Project $project): array
    {
        return $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'chart_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_accounts', 'id')
                    ->where(fn ($query) => $query
                        ->where('company_id', $project->company_id)
                        ->where('is_active', true)),
            ],
            'vendor_id' => [
                'required',
                'integer',
                Rule::exists('project_vendors', 'id')
                    ->where(fn ($query) => $query->where('project_id', $project->id)),
            ],
            'expense_date' => ['required', 'date'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function assertExpenseBelongsToProject(Project $project, ProjectExpense $expense): void
    {
        if ((int) $project->id !== (int) $expense->project_id) {
            abort(404);
        }
    }

    private function assertExpenseCanBeManagedFromProject(ProjectExpense $expense): void
    {
        if ($expense->expense_source === ProjectExpense::SOURCE_BUDGET_PLAN_REALIZATION) {
            throw ValidationException::withMessages([
                'expense' => 'Expense dari realisasi BP hanya bisa diubah dari modul realisasi BP.',
            ]);
        }
    }
}
