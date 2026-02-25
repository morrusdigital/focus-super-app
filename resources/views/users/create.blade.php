@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Tambah User</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen User</a></li>
            <li class="breadcrumb-item active">Tambah</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('users.store') }}">
      @csrf
      <div class="card">
        <div class="card-header pb-0">
          <h5>Data User</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama</label>
              <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" value="{{ old('name') }}" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email') }}" required>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input class="form-control @error('password') is-invalid @enderror" name="password" type="password" required>
              @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Konfirmasi Password</label>
              <input class="form-control" name="password_confirmation" type="password" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Role</label>
              <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                <option value="">-- Pilih --</option>
                @foreach ($roles as $role)
                  <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->value }}</option>
                @endforeach
              </select>
              @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @if (auth()->user()->isHoldingAdmin())
              <div class="col-md-4">
                <label class="form-label">Perusahaan</label>
                <select class="form-select @error('company_id') is-invalid @enderror" name="company_id" required>
                  <option value="">-- Pilih --</option>
                  @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>{{ $company->name }}</option>
                  @endforeach
                </select>
                @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            @endif
          </div>
        </div>
        <div class="card-footer text-end">
          <a class="btn btn-light" href="{{ route('users.index') }}">Batal</a>
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </div>
    </form>
  </div>
@endsection
