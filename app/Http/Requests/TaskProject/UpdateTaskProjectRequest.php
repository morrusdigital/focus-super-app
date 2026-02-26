<?php

namespace App\Http\Requests\TaskProject;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'project_manager_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
