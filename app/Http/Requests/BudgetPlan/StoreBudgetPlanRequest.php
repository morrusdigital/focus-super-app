<?php

namespace App\Http\Requests\BudgetPlan;

use App\Models\BudgetPlan;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BudgetPlan::class) ?? false;
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
