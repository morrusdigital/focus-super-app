<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task $task */
        $task   = $this->route('task');
        $user   = $this->user();
        $status = $this->input('status');

        // Users who can fully update the task may move it anywhere.
        if ($user?->can('update', $task)) {
            return true;
        }

        // Assignees (members) may move their task to done only.
        if ($status === TaskStatus::Done->value && $user?->can('markDone', $task)) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'status'         => ['required', Rule::in(TaskStatus::values())],
            'blocked_reason' => [
                Rule::requiredIf(fn () => $this->input('status') === TaskStatus::Blocked->value),
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'         => 'Status target wajib dipilih.',
            'status.in'               => 'Status target tidak valid.',
            'blocked_reason.required' => 'Blocked reason wajib diisi jika status blocked.',
        ];
    }
}
