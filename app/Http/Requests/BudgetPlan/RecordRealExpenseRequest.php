<?php

namespace App\Http\Requests\BudgetPlan;

use App\Models\BudgetPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordRealExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $budgetPlan = $this->route('budget_plan');

        return $budgetPlan
            ? $this->user()?->can('recordExpense', $budgetPlan) ?? false
            : false;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.real_amount' => ['required', 'numeric', 'min:0'],
            'items.*.category' => [
                'required',
                Rule::exists('budget_plan_categories', 'name')->where('company_id', $this->user()?->company_id),
            ],
        ];
    }
}
