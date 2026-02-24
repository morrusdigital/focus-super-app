<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only users who can update the project may create tasks in it.
        $project = $this->route('project');

        return $this->user()?->can('update', $project) ?? false;
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title'          => ['required', 'string', 'max:255'],
            'status'         => ['required', Rule::in(TaskStatus::values())],
            'progress'       => ['required', 'integer', 'min:0', 'max:100'],
            'due_date'       => ['nullable', 'date'],
            'blocked_reason' => [
                Rule::requiredIf(fn () => $this->input('status') === TaskStatus::Blocked->value),
                'nullable',
                'string',
            ],
            'assignees'      => ['required', 'array', 'min:1'],
            'assignees.*'    => [
                'required',
                'integer',
                Rule::exists('project_members', 'user_id')
                    ->where('project_id', $project->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'assignees.required'   => 'Minimal satu assignee harus dipilih.',
            'assignees.min'        => 'Minimal satu assignee harus dipilih.',
            'assignees.*.exists'   => 'Assignee harus merupakan member project ini.',
            'blocked_reason.required' => 'Blocked reason wajib diisi jika status blocked.',
        ];
    }
}
