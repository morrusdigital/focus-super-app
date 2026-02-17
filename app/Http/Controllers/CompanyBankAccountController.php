<?php

namespace App\Http\Controllers;

use App\Models\CompanyBankAccount;
use Illuminate\Http\Request;

class CompanyBankAccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', CompanyBankAccount::class);

        $bankAccounts = CompanyBankAccount::query()
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return view('bank_accounts.index', [
            'bankAccounts' => $bankAccounts,
        ]);
    }

    public function create()
    {
        $this->authorize('create', CompanyBankAccount::class);

        return view('bank_accounts.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', CompanyBankAccount::class);

        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $bankAccount = CompanyBankAccount::create([
            'company_id' => $request->user()->company_id,
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);

        if ($bankAccount->is_default) {
            CompanyBankAccount::where('company_id', $request->user()->company_id)
                ->where('id', '!=', $bankAccount->id)
                ->update(['is_default' => false]);
        }

        return redirect()->route('bank-accounts.index');
    }

    public function show(CompanyBankAccount $bankAccount)
    {
        $this->authorize('view', $bankAccount);

        return view('bank_accounts.show', [
            'bankAccount' => $bankAccount,
        ]);
    }

    public function edit(CompanyBankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        return view('bank_accounts.edit', [
            'bankAccount' => $bankAccount,
        ]);
    }

    public function update(Request $request, CompanyBankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $bankAccount->update([
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name' => $data['account_name'],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);

        if ($bankAccount->is_default) {
            CompanyBankAccount::where('company_id', $request->user()->company_id)
                ->where('id', '!=', $bankAccount->id)
                ->update(['is_default' => false]);
        }

        return redirect()->route('bank-accounts.show', $bankAccount);
    }

    public function destroy(CompanyBankAccount $bankAccount)
    {
        $this->authorize('delete', $bankAccount);

        $bankAccount->delete();

        return redirect()->route('bank-accounts.index');
    }
}
