<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatchTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task $task */
        $task   = $this->route('task');
        $user   = $this->user();
        $status = $this->input('status');

        if ($user?->can('update', $task)) {
            return true;
        }

        // Members may markDone if they are an assignee and target status is done.
        if ($status === TaskStatus::Done->value && $user?->can('markDone', $task)) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'status'         => ['required', Rule::in(TaskStatus::values())],
            'progress'       => ['required', 'integer', 'min:0', 'max:100'],
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
            'blocked_reason.required' => 'Blocked reason wajib diisi jika status blocked.',
        ];
    }
}
