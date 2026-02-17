<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use Illuminate\Http\Request;

class ChartAccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ChartAccount::class);

        $accounts = ChartAccount::query()
            ->where('company_id', $request->user()->company_id)
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        return view('chart_accounts.index', [
            'accounts' => $accounts,
        ]);
    }

    public function create()
    {
        $this->authorize('create', ChartAccount::class);

        return view('chart_accounts.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', ChartAccount::class);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ChartAccount::create([
            'company_id' => $request->user()->company_id,
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('chart-accounts.index');
    }

    public function show(ChartAccount $chartAccount)
    {
        $this->authorize('view', $chartAccount);

        return view('chart_accounts.show', [
            'account' => $chartAccount,
        ]);
    }

    public function edit(ChartAccount $chartAccount)
    {
        $this->authorize('update', $chartAccount);

        return view('chart_accounts.edit', [
            'account' => $chartAccount,
        ]);
    }

    public function update(Request $request, ChartAccount $chartAccount)
    {
        $this->authorize('update', $chartAccount);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $chartAccount->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('chart-accounts.show', $chartAccount);
    }

    public function destroy(ChartAccount $chartAccount)
    {
        $this->authorize('delete', $chartAccount);

        $chartAccount->delete();

        return redirect()->route('chart-accounts.index');
    }
}
