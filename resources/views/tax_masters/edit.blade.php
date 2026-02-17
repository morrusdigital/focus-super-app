@extends('layouts.app')

@section('title', 'Ubah Master Pajak')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Ubah Master Pajak</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tax-masters.index') }}">Master Pajak</a></li>
            <li class="breadcrumb-item active">Ubah</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('tax-masters.update', $taxMaster) }}">
      @csrf
      @method('put')
      <div class="card">
        <div class="card-header pb-0">
          <h5>Detail Pajak</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Jenis Pajak</label>
              <select class="form-select" name="tax_type" required>
                <option value="">-- Pilih --</option>
                <option value="pph" @selected(old('tax_type', $taxMaster->tax_type) === 'pph')>PPH</option>
                <option value="ppn" @selected(old('tax_type', $taxMaster->tax_type) === 'ppn')>PPN</option>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label">Nama Tarif</label>
              <input class="form-control" name="name" type="text" value="{{ old('name', $taxMaster->name) }}" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Persentase</label>
              <input class="form-control text-end" name="percentage" type="number" step="0.01" min="0" max="100" value="{{ old('percentage', $taxMaster->percentage) }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-center">
              <div class="form-check mt-4">
                <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $taxMaster->is_active))>
                <label class="form-check-label" for="is_active">Aktif</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body text-end">
          <a class="btn btn-light" href="{{ route('tax-masters.show', $taxMaster) }}">Batal</a>
          <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
@endsection
