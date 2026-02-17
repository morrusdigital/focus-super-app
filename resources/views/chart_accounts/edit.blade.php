@extends('layouts.app')

@section('title', 'Ubah Akun')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Ubah Akun</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chart-accounts.index') }}">Akun</a></li>
            <li class="breadcrumb-item active">Ubah</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('chart-accounts.update', $account) }}">
      @csrf
      @method('put')
      <div class="card">
        <div class="card-header pb-0">
          <h5>Detail Akun</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Kode</label>
              <input class="form-control" name="code" type="text" value="{{ old('code', $account->code) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nama</label>
              <input class="form-control" name="name" type="text" value="{{ old('name', $account->name) }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-center">
              <div class="form-check mt-4">
                <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $account->is_active))>
                <label class="form-check-label" for="is_active">Aktif</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-body text-end">
          <a class="btn btn-light" href="{{ route('chart-accounts.show', $account) }}">Batal</a>
          <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
@endsection
