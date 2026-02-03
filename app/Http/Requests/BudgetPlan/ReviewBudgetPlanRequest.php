<?php

namespace App\Http\Requests\BudgetPlan;

use App\Models\BudgetPlan;
use Illuminate\Foundation\Http\FormRequest;

class ReviewBudgetPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $budgetPlan = $this->route('budget_plan');

        if (! $budgetPlan) {
            return false;
        }

        $eligible = $budgetPlan->status === BudgetPlan::STATUS_SUBMITTED;

        if (! $eligible) {
            return false;
        }

        $name = $this->route()?->getName();
        $ability = match ($name) {
            'budget-plans.approve' => 'approve',
            'budget-plans.reject' => 'reject',
            'budget-plans.request-revision' => 'requestRevision',
            default => null,
        };

        if (! $ability) {
            return false;
        }

        return $this->user()?->can($ability, $budgetPlan) ?? false;
    }

    public function rules(): array
    {
        $name = $this->route()?->getName();
        $noteRequired = in_array($name, [
            'budget-plans.reject',
            'budget-plans.request-revision',
        ], true);

        return [
            'note' => [$noteRequired ? 'required' : 'nullable', 'string'],
        ];
    }
}
