<?php

namespace App\Http\Requests\BudgetPlan;

use App\Models\BudgetPlan;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'tanggal' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.kode' => ['required', 'string', 'max:100'],
            'items.*.vendor_name' => ['nullable', 'string', 'max:255'],
            'items.*.harsat' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
            'items.*.satuan' => ['required', 'string', 'max:50'],
        ];
    }
}
