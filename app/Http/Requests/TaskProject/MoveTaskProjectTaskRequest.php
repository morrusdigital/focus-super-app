<?php

namespace App\Http\Requests\TaskProject;

use App\Enums\TaskStatus;
use App\Models\TaskProjectTask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TaskProjectTask $task */
        $task = $this->route('task');

        // Full move: PM / admin roles
        // Mark-done only: assignees
        return $this->user()?->can('moveStatus', $task) ?? false;
    }

    public function rules(): array
    {
        return [
            'status'         => ['required', Rule::in(TaskStatus::values())],
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
