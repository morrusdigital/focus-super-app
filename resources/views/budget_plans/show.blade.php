@extends('layouts.app')

@section('title', 'Detail Budget Plan')

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
          <h3>Detail Budget Plan</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('budget-plans.index') }}">Budget Plan</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <div>
          <h5>{{ $budgetPlan->bp_number }}</h5>
          <span class="badge {{ $statusBadges[$budgetPlan->status] ?? 'badge-light-secondary' }}">
            {{ $statusLabels[$budgetPlan->status] ?? $budgetPlan->status }}
          </span>
        </div>
        <div class="d-flex gap-2">
          @can('update', $budgetPlan)
            <a class="btn btn-primary" href="{{ route('budget-plans.edit', $budgetPlan) }}">Edit</a>
          @endcan
          @can('delete', $budgetPlan)
            <form method="post" action="{{ route('budget-plans.destroy', $budgetPlan) }}" onsubmit="return confirm('Hapus Budget Plan ini?')">
              @csrf
              @method('delete')
              <button class="btn btn-danger" type="submit">Hapus</button>
            </form>
          @endcan
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted">Perusahaan</div>
            <div class="fw-bold">{{ $budgetPlan->company->name ?? '-' }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Pemohon</div>
            <div class="fw-bold">{{ $budgetPlan->requester->name ?? '-' }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Total</div>
            <div class="fw-bold">Rp {{ number_format($budgetPlan->total_amount, 2, ',', '.') }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Tanggal BP</div>
            <div class="fw-bold">{{ $budgetPlan->tanggal?->format('d/m/Y') ?? '-' }}</div>
          </div>
          <div class="col-md-12">
            <div class="text-muted">Catatan</div>
            <div>{{ $budgetPlan->notes ?: '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0">
        <h5>Items</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>ITEM</th>
                <th>KODE</th>
                <th>VENDOR</th>
                <th class="text-end">HARSAT</th>
                <th class="text-end">QTY</th>
                <th>SATUAN</th>
                <th class="text-end">JUMLAH</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($budgetPlan->items as $item)
                <tr>
                  <td>{{ $item->item_name }}</td>
                  <td>{{ $item->kode }}</td>
                  <td>{{ $item->vendor_name ?? '-' }}</td>
                  <td class="text-end">{{ number_format($item->harsat, 2, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($item->qty, 2, ',', '.') }}</td>
                  <td>{{ $item->satuan }}</td>
                  <td class="text-end">{{ number_format($item->jumlah, 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center">Belum ada item.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0">
        <h5>Log</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Aktor</th>
                <th>Aksi</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($budgetPlan->logs as $log)
                <tr>
                  <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                  <td>{{ $log->actor->name ?? '-' }}</td>
                  <td>{{ $log->action }}</td>
                  <td>{{ $log->note ?? '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center">Belum ada log.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0">
        <h5>Aksi</h5>
      </div>
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
          @can('submit', $budgetPlan)
            <form method="post" action="{{ route('budget-plans.submit', $budgetPlan) }}">
              @csrf
              <button class="btn btn-primary" type="submit">Ajukan</button>
            </form>
          @endcan

          @can('approve', $budgetPlan)
            <form method="post" action="{{ route('budget-plans.approve', $budgetPlan) }}">
              @csrf
              <button class="btn btn-success" type="submit">Setujui</button>
            </form>
          @endcan

          @can('reject', $budgetPlan)
            <form method="post" action="{{ route('budget-plans.reject', $budgetPlan) }}" class="d-flex gap-2">
              @csrf
              <input class="form-control" name="note" type="text" placeholder="Catatan penolakan" required>
              <button class="btn btn-danger" type="submit">Tolak</button>
            </form>
          @endcan

          @can('requestRevision', $budgetPlan)
            <form method="post" action="{{ route('budget-plans.request-revision', $budgetPlan) }}" class="d-flex gap-2">
              @csrf
              <input class="form-control" name="note" type="text" placeholder="Catatan revisi" required>
              <button class="btn btn-warning" type="submit">Minta Revisi</button>
            </form>
          @endcan
        </div>
      </div>
    </div>
  </div>
@endsection
