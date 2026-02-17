@extends('layouts.app')

@section('title', 'Budget Plan')

@php
  $statusLabels = [
      'draft' => 'Draft',
      'submitted' => 'Diajukan',
      'approved' => 'Disetujui',
      'rejected' => 'Ditolak',
      'revision_requested' => 'Perlu Revisi',
  ];

  $statusBadges = [
      'draft' => 'badge-light-secondary',
      'submitted' => 'badge-light-primary',
      'approved' => 'badge-light-success',
      'rejected' => 'badge-light-danger',
      'revision_requested' => 'badge-light-warning',
  ];
@endphp

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Budget Plan</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Budget Plan</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0">
        <div class="d-flex align-items-center justify-content-between">
          <h5>Daftar Budget Plan</h5>
          <div class="d-flex gap-2">
            <a class="btn btn-light" href="{{ route('budget-plans.pdf', request()->query()) }}">Export PDF</a>
            @can('create', App\Models\BudgetPlan::class)
              <a class="btn btn-primary" href="{{ route('budget-plans.create') }}">Tambah BP</a>
            @endcan
          </div>
        </div>
      </div>
      <div class="card-body">
        <form class="row g-3 align-items-end mb-3" method="get">
          <div class="col-sm-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="">Semua</option>
              @foreach ($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-4">
            <button class="btn btn-primary" type="submit">Filter</button>
            <a class="btn btn-light" href="{{ route('budget-plans.index') }}">Reset</a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>No. BP</th>
                <th>Perusahaan</th>
                <th>Pemohon</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal Pengajuan</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($budgetPlans as $bp)
                <tr>
                  <td>{{ $bp->bp_number }}</td>
                  <td>{{ $bp->company->name ?? '-' }}</td>
                  <td>{{ $bp->requester->name ?? '-' }}</td>
                  <td>Rp {{ number_format($bp->total_amount, 2, ',', '.') }}</td>
                  <td>
                    <span class="badge {{ $statusBadges[$bp->status] ?? 'badge-light-secondary' }}">
                      {{ $statusLabels[$bp->status] ?? $bp->status }}
                    </span>
                  </td>
                  <td>{{ $bp->submission_date?->format('d/m/Y') ?? '-' }}</td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-light" href="{{ route('budget-plans.show', $bp) }}">Detail</a>
                    @can('update', $bp)
                      <a class="btn btn-sm btn-primary" href="{{ route('budget-plans.edit', $bp) }}">Edit</a>
                    @endcan
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center">Belum ada data.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
