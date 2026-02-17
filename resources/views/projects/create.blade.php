@extends('layouts.app')

@section('title', 'Buat Project')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Buat Project</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Buat</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('projects.store') }}">
      @csrf
      <div class="card">
        <div class="card-header pb-0">
          <h5>Detail Project</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Nama Project</label>
              <input class="form-control" name="name" type="text" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Alamat Project</label>
              <input class="form-control" name="address" type="text" value="{{ old('address') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tanggal Start Kerja</label>
              <input class="form-control" name="start_work_date" type="date" value="{{ old('start_work_date') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Nilai Kontrak SPK</label>
              <input class="form-control text-end" name="contract_value" type="number" step="0.01" min="0" value="{{ old('contract_value') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">PPH</label>
              <select class="form-select" id="use_pph" name="use_pph" required>
                <option value="">-- Pilih --</option>
                <option value="1" @selected(old('use_pph') === '1')>Ya</option>
                <option value="0" @selected(old('use_pph') === '0')>Tidak</option>
              </select>
            </div>
            <div class="col-md-4 d-none" id="pph_tax_wrapper">
              <label class="form-label">Tarif PPH</label>
              <select class="form-select" id="pph_tax_master_id" name="pph_tax_master_id">
                <option value="">-- Pilih Tarif PPH --</option>
                @foreach ($pphTaxMasters as $taxMaster)
                  <option value="{{ $taxMaster->id }}" @selected((string) old('pph_tax_master_id') === (string) $taxMaster->id)>
                    {{ $taxMaster->name }} ({{ number_format($taxMaster->percentage, 2, ',', '.') }}%)
                  </option>
                @endforeach
              </select>
              @if ($pphTaxMasters->isEmpty())
                <small class="text-muted d-block mt-1">Belum ada master PPH aktif. Tambahkan di menu Master Pajak.</small>
              @endif
            </div>
            <div class="col-md-4">
              <label class="form-label">PPN</label>
              <select class="form-select" id="use_ppn" name="use_ppn" required>
                <option value="">-- Pilih --</option>
                <option value="1" @selected(old('use_ppn') === '1')>Ya</option>
                <option value="0" @selected(old('use_ppn') === '0')>Tidak</option>
              </select>
            </div>
            <div class="col-md-4 d-none" id="ppn_tax_wrapper">
              <label class="form-label">Tarif PPN</label>
              <select class="form-select" id="ppn_tax_master_id" name="ppn_tax_master_id">
                <option value="">-- Pilih Tarif PPN --</option>
                @foreach ($ppnTaxMasters as $taxMaster)
                  <option value="{{ $taxMaster->id }}" @selected((string) old('ppn_tax_master_id') === (string) $taxMaster->id)>
                    {{ $taxMaster->name }} ({{ number_format($taxMaster->percentage, 2, ',', '.') }}%)
                  </option>
                @endforeach
              </select>
              @if ($ppnTaxMasters->isEmpty())
                <small class="text-muted d-block mt-1">Belum ada master PPN aktif. Tambahkan di menu Master Pajak.</small>
              @endif
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <a class="btn btn-light" href="{{ route('projects.index') }}">Batal</a>
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      const usePphField = document.getElementById('use_pph');
      const pphWrapper = document.getElementById('pph_tax_wrapper');
      const pphSelect = document.getElementById('pph_tax_master_id');

      const usePpnField = document.getElementById('use_ppn');
      const ppnWrapper = document.getElementById('ppn_tax_wrapper');
      const ppnSelect = document.getElementById('ppn_tax_master_id');

      function toggleTaxField(useField, wrapper, selectField) {
        if (!useField || !wrapper || !selectField) {
          return;
        }

        const enabled = useField.value === '1';
        wrapper.classList.toggle('d-none', !enabled);
        selectField.required = enabled;

        if (!enabled) {
          selectField.value = '';
        }
      }

      toggleTaxField(usePphField, pphWrapper, pphSelect);
      toggleTaxField(usePpnField, ppnWrapper, ppnSelect);

      usePphField?.addEventListener('change', function () {
        toggleTaxField(usePphField, pphWrapper, pphSelect);
      });

      usePpnField?.addEventListener('change', function () {
        toggleTaxField(usePpnField, ppnWrapper, ppnSelect);
      });
    })();
  </script>
@endpush
