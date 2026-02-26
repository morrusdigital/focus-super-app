<?php

namespace App\Http\Requests\TaskProject;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskProjectTaskRequest extends FormRequest
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
            'assignees'      => ['nullable', 'array'],
            'assignees.*'    => ['integer', 'exists:users,id'],
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
            'assignees.*.exists'      => 'Assignee tidak ditemukan.',
            'blocked_reason.required' => 'Blocked reason wajib diisi jika status blocked.',
        ];
    }
}
