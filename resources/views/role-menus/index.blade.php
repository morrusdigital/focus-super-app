@extends('layouts.app')

@section('title', 'Manajemen Role Menu')

@section('content')
  <div class="container-fluid">
    {{-- Page title --}}
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Manajemen Role Menu</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Manajemen Role Menu</li>
          </ol>
        </div>
      </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Info banner --}}
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
      <i data-feather="info" style="min-width:18px;margin-top:2px;"></i>
      <div>
        <strong>Tentang fitur ini:</strong> Atur menu mana saja yang ditampilkan di sidebar untuk setiap role.
        Role <strong>Holding Admin</strong> selalu mendapatkan akses penuh ke semua menu dan tidak dapat diubah.
        Perubahan langsung berlaku saat user berikutnya memuat halaman.
      </div>
    </div>

    {{-- Role cards grid --}}
    <div class="row g-3">
      @foreach ($roleData as $role => $data)
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
              <h5 class="mb-0">
                <i data-feather="user" class="me-1" style="width:18px;height:18px;"></i>
                {{ $data['label'] }}
              </h5>
              <span class="badge bg-primary">{{ $role }}</span>
            </div>
            <div class="card-body">
              {{-- Progress bar --}}
              @php
                $pct = $data['total_items'] > 0
                  ? round(($data['menu_count'] / $data['total_items']) * 100)
                  : 0;
              @endphp
              <div class="d-flex align-items-center justify-content-between mb-1">
                <small class="text-muted">
                  {{ $data['menu_count'] }} dari {{ $data['total_items'] }} menu aktif
                </small>
                <small class="text-muted">{{ $pct }}%</small>
              </div>
              <div class="progress mb-3" style="height:6px;">
                <div class="progress-bar bg-primary" style="width: {{ $pct }}%;"></div>
              </div>

              {{-- Active menu badges --}}
              <div class="d-flex flex-wrap gap-1 mb-3">
                @forelse ($data['menu_keys'] as $key)
                  @php $item = $catalog[$key] ?? null; @endphp
                  @if ($item)
                    <span class="badge rounded-pill"
                          style="background:#e8f0fe;color:#1a56db;font-weight:500;font-size:.75rem;">
                      <i data-feather="{{ $item['icon'] }}" style="width:11px;height:11px;margin-right:3px;"></i>
                      {{ $item['label'] }}
                    </span>
                  @endif
                @empty
                  <span class="text-muted small fst-italic">Belum ada menu yang diaktifkan</span>
                @endforelse
              </div>
            </div>
            <div class="card-footer bg-transparent pt-0">
              <a href="{{ route('role-menus.edit', $role) }}"
                 class="btn btn-primary btn-sm w-100">
                <i data-feather="edit-2" style="width:14px;height:14px;margin-right:4px;"></i>
                Atur Menu Role Ini
              </a>
            </div>
          </div>
        </div>
      @endforeach

      {{-- Holding Admin card (fixed, read-only) --}}
      <div class="col-md-6">
        <div class="card h-100 border-warning">
          <div class="card-header pb-0 d-flex align-items-center justify-content-between bg-warning bg-opacity-10">
            <h5 class="mb-0">
              <i data-feather="shield" class="me-1" style="width:18px;height:18px;"></i>
              Holding Admin
            </h5>
            <span class="badge bg-warning text-dark">holding_admin</span>
          </div>
          <div class="card-body">
            <div class="progress mb-3" style="height:6px;">
              <div class="progress-bar bg-warning" style="width:100%;"></div>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-3">
              @foreach ($catalog as $key => $item)
                <span class="badge rounded-pill"
                      style="background:#fff8e1;color:#b45309;font-weight:500;font-size:.75rem;">
                  <i data-feather="{{ $item['icon'] }}" style="width:11px;height:11px;margin-right:3px;"></i>
                  {{ $item['label'] }}
                </span>
              @endforeach
              {{-- +role_management --}}
              <span class="badge rounded-pill"
                    style="background:#fff8e1;color:#b45309;font-weight:500;font-size:.75rem;">
                <i data-feather="shield" style="width:11px;height:11px;margin-right:3px;"></i>
                Manajemen Role Menu
              </span>
            </div>
          </div>
          <div class="card-footer bg-transparent pt-0">
            <button class="btn btn-outline-warning btn-sm w-100" disabled>
              <i data-feather="lock" style="width:14px;height:14px;margin-right:4px;"></i>
              Akses Penuh — Tidak Dapat Diubah
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
