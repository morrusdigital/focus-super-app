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

    {{-- Filter Form --}}
    <div class="card mb-3">
      <div class="card-body py-3">
        <form method="get" action="{{ route('users.index') }}">
          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label mb-1 small">Nama</label>
              <input class="form-control form-control-sm" name="name" type="text"
                     value="{{ request('name') }}" placeholder="Cari nama...">
            </div>
            <div class="col-md-3">
              <label class="form-label mb-1 small">Email</label>
              <input class="form-control form-control-sm" name="email" type="text"
                     value="{{ request('email') }}" placeholder="Cari email...">
            </div>
            <div class="col-md-2">
              <label class="form-label mb-1 small">Role</label>
              <select class="form-select form-select-sm" name="role">
                <option value="">Semua Role</option>
                @foreach ($roles as $role)
                  <option value="{{ $role->value }}" @selected(request('role') === $role->value)>
                    {{ $role->value }}
                  </option>
                @endforeach
              </select>
            </div>
            @if (auth()->user()->isHoldingAdmin())
              <div class="col-md-2">
                <label class="form-label mb-1 small">Perusahaan</label>
                <select class="form-select form-select-sm" name="company_id">
                  <option value="">Semua Perusahaan</option>
                  @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(request('company_id') == $company->id)>
                      {{ $company->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            @endif
            <div class="col-md-2">
              <label class="form-label mb-1 small">Status</label>
              <select class="form-select form-select-sm" name="is_active">
                <option value="">Semua Status</option>
                <option value="1" @selected(request('is_active') === '1')>Aktif</option>
                <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
              </select>
            </div>
            <div class="col-auto d-flex gap-1">
              <button class="btn btn-sm btn-primary" type="submit">Filter</button>
              <a class="btn btn-sm btn-light" href="{{ route('users.index') }}">Reset</a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Daftar User <span class="text-muted fs-6 fw-normal">({{ $users->total() }} total)</span></h5>
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
                  <td colspan="6" class="text-center">Tidak ada user yang sesuai filter.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
          <div class="d-flex justify-content-end mt-3">
            {{ $users->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
