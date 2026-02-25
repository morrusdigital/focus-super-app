@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Edit User</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen User</a></li>
            <li class="breadcrumb-item active">Edit</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="post" action="{{ route('users.update', $user) }}">
      @csrf
      @method('put')
      <div class="card">
        <div class="card-header pb-0">
          <h5>Data User</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama</label>
              <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" value="{{ old('name', $user->name) }}" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $user->email) }}" required>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Role</label>
              <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                <option value="">-- Pilih --</option>
                @foreach ($roles as $role)
                  <option value="{{ $role->value }}" @selected(old('role', $user->role) === $role->value)>{{ $role->value }}</option>
                @endforeach
              </select>
              @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>
        </div>
        <div class="card-footer text-end">
          <a class="btn btn-light" href="{{ route('users.show', $user) }}">Batal</a>
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </div>
    </form>
  </div>
@endsection
