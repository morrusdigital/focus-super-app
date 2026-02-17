<?php

namespace App\Http\Requests\BudgetPlan;

use App\Models\BudgetPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $budgetPlan = $this->route('budget_plan');

        return $budgetPlan
            ? $this->user()?->can('update', $budgetPlan) ?? false
            : false;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'submission_date' => ['required', 'date'],
            'category' => [
                'required',
                Rule::exists('budget_plan_categories', 'name')->where('company_id', $companyId),
            ],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.project_id' => [
                'required',
                Rule::exists('projects', 'id')->where('company_id', $companyId),
            ],
            'items.*.bank_account_id' => [
                'required',
                Rule::exists('company_bank_accounts', 'id')->where('company_id', $companyId),
            ],
            'items.*.chart_account_id' => [
                'required',
                Rule::exists('chart_accounts', 'id')->where('company_id', $companyId),
            ],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.vendor_name' => ['nullable', 'string', 'max:255'],
            'items.*.category' => [
                'nullable',
                Rule::exists('budget_plan_categories', 'name')->where('company_id', $companyId),
            ],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.line_total' => ['nullable', 'numeric', 'min:0'],
            'items.*.real_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
