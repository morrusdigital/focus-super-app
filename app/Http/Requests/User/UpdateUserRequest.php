<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $target */
        $target = $this->route('user');

        return $this->user()?->can('update', $target) ?? false;
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');
        $actor  = $this->user();

        // Privilege escalation guard: same as store.
        $assignableRoles = $actor?->isHoldingAdmin()
            ? array_column(UserRole::cases(), 'value')
            : [
                UserRole::CompanyAdmin->value,
                UserRole::FinanceCompany->value,
                UserRole::Employee->value,
              ];

        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'role'       => ['required', Rule::in($assignableRoles)],
            // Explicitly block any attempt to change company via a crafted request.
            'company_id' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama wajib diisi.',
            'email.required'       => 'Email wajib diisi.',
            'email.unique'         => 'Email sudah digunakan.',
            'role.required'        => 'Role wajib dipilih.',
            'role.in'              => 'Role tidak valid atau tidak diizinkan untuk aktor ini.',
            'company_id.prohibited'=> 'Perubahan perusahaan user tidak diizinkan.',
        ];
    }
}
