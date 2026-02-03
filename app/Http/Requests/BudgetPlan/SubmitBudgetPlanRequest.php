<?php

namespace App\Http\Requests\BudgetPlan;

use App\Models\BudgetPlan;
use Illuminate\Foundation\Http\FormRequest;

class SubmitBudgetPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $budgetPlan = $this->route('budget_plan');

        if (! $budgetPlan) {
            return false;
        }

        $eligible = in_array($budgetPlan->status, [
            BudgetPlan::STATUS_DRAFT,
            BudgetPlan::STATUS_REVISION_REQUESTED,
        ], true);

        return $eligible && ($this->user()?->can('submit', $budgetPlan) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
