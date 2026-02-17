@extends('layouts.app')

@section('title', 'Tambah Rekening')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Tambah Rekening</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bank-accounts.index') }}">Rekening</a></li>
            <li class="breadcrumb-item active">Tambah</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('bank-accounts.store') }}">
      @csrf
      <div class="card">
        <div class="card-header pb-0">
          <h5>Detail Rekening</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Nama Bank</label>
              <input class="form-control" name="bank_name" type="text" value="{{ old('bank_name') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">No. Rekening</label>
              <input class="form-control" name="account_number" type="text" value="{{ old('account_number') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Atas Nama</label>
              <input class="form-control" name="account_name" type="text" value="{{ old('account_name') }}" required>
            </div>
            <div class="col-md-4">
              <div class="form-check mt-2">
                <input class="form-check-input" id="is_default" name="is_default" type="checkbox" value="1" @checked(old('is_default'))>
                <label class="form-check-label" for="is_default">Set sebagai rekening utama</label>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <a class="btn btn-light" href="{{ route('bank-accounts.index') }}">Batal</a>
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </div>
    </form>
  </div>
@endsection
