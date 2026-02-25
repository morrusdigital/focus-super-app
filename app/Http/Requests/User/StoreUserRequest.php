<?php

namespace App\Http\Requests\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    public function rules(): array
    {
        $actor = $this->user();

        // company_admin can only create users for their own company.
        // holding_admin can choose any company.
        $companyRule = $actor?->isHoldingAdmin()
            ? ['required', 'integer', 'exists:companies,id']
            : ['prohibited'];   // company_admin: no input allowed (set in controller)

        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'role'       => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'company_id' => $companyRule,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
            'company_id.required' => 'Perusahaan wajib dipilih.',
            'company_id.exists'   => 'Perusahaan tidak ditemukan.',
        ];
    }
}
