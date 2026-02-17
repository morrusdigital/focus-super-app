<?php

namespace App\Http\Controllers;

use App\Models\TaxMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxMasterController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', TaxMaster::class);

        $taxMasters = TaxMaster::query()
            ->orderBy('tax_type')
            ->orderBy('name')
            ->get();

        return view('tax_masters.index', [
            'taxMasters' => $taxMasters,
        ]);
    }

    public function create()
    {
        $this->authorize('create', TaxMaster::class);

        return view('tax_masters.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', TaxMaster::class);

        $data = $this->validatePayload($request);

        TaxMaster::create([
            'tax_type' => $data['tax_type'],
            'name' => $data['name'],
            'percentage' => $data['percentage'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('tax-masters.index');
    }

    public function show(TaxMaster $taxMaster)
    {
        $this->authorize('view', $taxMaster);

        return view('tax_masters.show', [
            'taxMaster' => $taxMaster,
        ]);
    }

    public function edit(TaxMaster $taxMaster)
    {
        $this->authorize('update', $taxMaster);

        return view('tax_masters.edit', [
            'taxMaster' => $taxMaster,
        ]);
    }

    public function update(Request $request, TaxMaster $taxMaster)
    {
        $this->authorize('update', $taxMaster);

        $data = $this->validatePayload($request, $taxMaster);

        $taxMaster->update([
            'tax_type' => $data['tax_type'],
            'name' => $data['name'],
            'percentage' => $data['percentage'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('tax-masters.show', $taxMaster);
    }

    public function destroy(TaxMaster $taxMaster)
    {
        $this->authorize('delete', $taxMaster);

        $taxMaster->delete();

        return redirect()->route('tax-masters.index');
    }

    private function validatePayload(Request $request, ?TaxMaster $taxMaster = null): array
    {
        return $request->validate([
            'tax_type' => [
                'required',
                Rule::in([TaxMaster::TYPE_PPH, TaxMaster::TYPE_PPN]),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('tax_masters', 'name')
                    ->where(fn ($query) => $query->where('tax_type', $request->input('tax_type')))
                    ->ignore($taxMaster?->id),
            ],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
