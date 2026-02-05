<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('card'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['string', 'max:50'],
        ];
    }
}
