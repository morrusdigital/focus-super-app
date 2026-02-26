<?php

namespace App\Http\Requests\TaskProject;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'status'         => ['required', Rule::in(TaskStatus::values())],
            'progress'       => ['required', 'integer', 'min:0', 'max:100'],
            'due_date'       => ['nullable', 'date'],
            'assignees'      => ['required', 'array', 'min:1'],
            'assignees.*'    => ['required', 'integer', 'exists:users,id'],
            // blocked_reason is required when status is blocked
            'blocked_reason' => [
                Rule::requiredIf(fn () => $this->input('status') === TaskStatus::Blocked->value),
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'assignees.required'      => 'Minimal satu assignee harus dipilih.',
            'assignees.min'           => 'Minimal satu assignee harus dipilih.',
            'assignees.*.exists'      => 'Assignee tidak ditemukan.',
            'blocked_reason.required' => 'Blocked reason wajib diisi jika status blocked.',
        ];
    }
}
