<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()?->can('manageMembers', $project) ?? false;
    }

    public function rules(): array
    {
        $project    = $this->route('project');
        $actingUser = $this->user();

        // Base existence rule — for holding_admin cross-company adds are allowed
        $existsRule = Rule::exists('users', 'id');

        if (!$actingUser?->isHoldingAdmin()) {
            $existsRule->where('company_id', $project->company_id);
        }

        // Reject duplicates
        $uniqueRule = Rule::unique('project_members', 'user_id')
            ->where('project_id', $project->id);

        return [
            'user_id' => ['required', 'integer', $existsRule, $uniqueRule],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User harus dipilih.',
            'user_id.exists'   => 'User tidak ditemukan atau berasal dari company yang berbeda.',
            'user_id.unique'   => 'User sudah menjadi member project ini.',
        ];
    }
}
