<?php

namespace App\Http\Controllers;

use App\Models\BudgetPlanCategory;
use Illuminate\Http\Request;

class BudgetPlanCategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', BudgetPlanCategory::class);

        $categories = BudgetPlanCategory::query()
            ->where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->get();

        return view('budget_plan_categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->authorize('create', BudgetPlanCategory::class);

        return view('budget_plan_categories.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', BudgetPlanCategory::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        BudgetPlanCategory::create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('budget-plan-categories.index');
    }

    public function show(BudgetPlanCategory $budgetPlanCategory)
    {
        $this->authorize('view', $budgetPlanCategory);

        return view('budget_plan_categories.show', [
            'category' => $budgetPlanCategory,
        ]);
    }

    public function edit(BudgetPlanCategory $budgetPlanCategory)
    {
        $this->authorize('update', $budgetPlanCategory);

        return view('budget_plan_categories.edit', [
            'category' => $budgetPlanCategory,
        ]);
    }

    public function update(Request $request, BudgetPlanCategory $budgetPlanCategory)
    {
        $this->authorize('update', $budgetPlanCategory);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $budgetPlanCategory->update([
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('budget-plan-categories.show', $budgetPlanCategory);
    }

    public function destroy(BudgetPlanCategory $budgetPlanCategory)
    {
        $this->authorize('delete', $budgetPlanCategory);

        $budgetPlanCategory->delete();

        return redirect()->route('budget-plan-categories.index');
    }
}
