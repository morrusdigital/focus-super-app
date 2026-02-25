@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Manajemen User</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Manajemen User</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Daftar User</h5>
        @can('create', App\Models\User::class)
          <a class="btn btn-primary" href="{{ route('users.create') }}">Tambah User</a>
        @endcan
      </div>
      <div class="card-body">
        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Perusahaan</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $user)
                <tr>
                  <td>{{ $user->name }}</td>
                  <td>{{ $user->email }}</td>
                  <td>{{ $user->role }}</td>
                  <td>{{ $user->company?->name ?? '-' }}</td>
                  <td>
                    <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                      {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="text-end">
                    @can('view', $user)
                      <a class="btn btn-sm btn-light" href="{{ route('users.show', $user) }}">Detail</a>
                    @endcan
                    @can('update', $user)
                      <a class="btn btn-sm btn-primary" href="{{ route('users.edit', $user) }}">Edit</a>
                    @endcan
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center">Belum ada user.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
