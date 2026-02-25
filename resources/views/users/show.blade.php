@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Detail User</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Manajemen User</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>{{ $user->name }}</h5>
        <div class="d-flex gap-2">
          @can('update', $user)
            <a class="btn btn-primary btn-sm" href="{{ route('users.edit', $user) }}">Edit</a>
          @endcan
          @can('activate', $user)
            <form method="post" action="{{ route('users.activate', $user) }}">
              @csrf
              <button class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}" type="submit">
                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </form>
          @endcan
        </div>
      </div>
      <div class="card-body">
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-borderless">
          <tr>
            <th width="160">Nama</th>
            <td>{{ $user->name }}</td>
          </tr>
          <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
          </tr>
          <tr>
            <th>Role</th>
            <td>{{ $user->role }}</td>
          </tr>
          <tr>
            <th>Perusahaan</th>
            <td>{{ $user->company?->name ?? '-' }}</td>
          </tr>
          <tr>
            <th>Status</th>
            <td>
              <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
          </tr>
        </table>
      </div>
    </div>

    @can('resetPassword', $user)
      <div class="card mt-3">
        <div class="card-header pb-0">
          <h5>Reset Password</h5>
        </div>
        <form method="post" action="{{ route('users.reset-password', $user) }}">
          @csrf
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Password Baru</label>
                <input class="form-control @error('password') is-invalid @enderror" name="password" type="password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Konfirmasi Password</label>
                <input class="form-control" name="password_confirmation" type="password" required>
              </div>
            </div>
          </div>
          <div class="card-footer text-end">
            <button class="btn btn-warning" type="submit">Reset Password</button>
          </div>
        </form>
      </div>
    @endcan
  </div>
@endsection
