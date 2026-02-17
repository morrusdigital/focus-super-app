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

  $categories = $categories ?? collect();
  $submissionDate = $budgetPlan->submission_date;
  $weekOfMonthDisplay = $budgetPlan->week_of_month;
  if ($weekOfMonthDisplay === null && $submissionDate) {
      $firstOfMonth = $submissionDate->copy()->startOfMonth();
      $offset = $firstOfMonth->dayOfWeekIso - 1;
      $weekOfMonthDisplay = intdiv($offset + ($submissionDate->day - 1), 7) + 1;
  }
  $projectCountDisplay = $budgetPlan->project_count ?? $budgetPlan->items->pluck('project_id')->filter()->unique()->count();
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
          <a class="btn btn-light" href="{{ route('budget-plans.pdf.show', $budgetPlan) }}">Export PDF</a>
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
            <div class="text-muted">Tanggal Pengajuan</div>
            <div class="fw-bold">{{ $submissionDate?->format('d/m/Y') ?? '-' }}</div>
          </div>
          <div class="col-md-2">
            <div class="text-muted">Minggu ke-</div>
            <div class="fw-bold">{{ $weekOfMonthDisplay ?? '-' }}</div>
          </div>
          <div class="col-md-2">
            <div class="text-muted">Jumlah Project</div>
            <div class="fw-bold">{{ $projectCountDisplay ?? '-' }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Kategori</div>
            <div class="fw-bold">{{ $budgetPlan->category ?? '-' }}</div>
          </div>
          <div class="col-md-12">
            <div class="text-muted">Catatan</div>
            <div>{{ $budgetPlan->notes ?: '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    @if (auth()->user()?->isFinanceHolding() && $summary)
      <div class="card">
        <div class="card-header pb-0">
          <h5>Rekap Perusahaan</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="text-muted">Saldo Awal</div>
              <div class="fw-bold">Rp {{ number_format($summary['saldo_awal'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-4">
              <div class="text-muted">Total Real Expense</div>
              <div class="fw-bold">Rp {{ number_format($summary['total_real_expense'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-4">
              <div class="text-muted">Saldo Berjalan</div>
              <div class="fw-bold">Rp {{ number_format($summary['saldo_berjalan'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-4">
              <div class="text-muted">Total Request</div>
              <div class="fw-bold">Rp {{ number_format($summary['total_request'], 2, ',', '.') }}</div>
            </div>
            <div class="col-md-4">
              <div class="text-muted">Balance Due</div>
              <div class="fw-bold">Rp {{ number_format($summary['balance_due'], 2, ',', '.') }}</div>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-header pb-0">
        <h5>Items</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>PROJECT</th>
                <th>AKUN</th>
                <th>REKENING</th>
                <th>KATEGORI</th>
                <th>ITEM</th>
                <th>VENDOR</th>
                <th class="text-end">HARSAT</th>
                <th class="text-end">QTY</th>
                <th>SATUAN</th>
                <th class="text-end">JUMLAH</th>
                <th class="text-end">REALISASI</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($budgetPlan->items as $item)
                <tr>
                  <td>{{ $item->project->name ?? '-' }}</td>
                  <td>{{ $item->chartAccount ? $item->chartAccount->code . ' - ' . $item->chartAccount->name : '-' }}</td>
                  <td>
                    @if ($item->bankAccount)
                      {{ $item->bankAccount->bank_name }} - {{ $item->bankAccount->account_number }}
                    @else
                      -
                    @endif
                  </td>
                  <td>{{ $item->category ?? '-' }}</td>
                  <td>{{ $item->item_name }}</td>
                  <td>{{ $item->vendor_name ?? '-' }}</td>
                  <td class="text-end">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                  <td>{{ $item->unit }}</td>
                  <td class="text-end">{{ number_format($item->line_total, 2, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($item->real_amount, 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center">Belum ada item.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    @can('recordExpense', $budgetPlan)
      <div class="card">
        <div class="card-header pb-0">
          <h5>Input Real Expense</h5>
        </div>
        <div class="card-body">
          <form method="post" action="{{ route('budget-plans.record-expense', $budgetPlan) }}">
            @csrf
            <div class="table-responsive">
              <table class="table table-bordered align-middle">
                <thead>
                  <tr>
                    <th>PROJECT</th>
                    <th>AKUN</th>
                    <th>REKENING</th>
                    <th>ITEM</th>
                    <th>VENDOR</th>
                    <th>KATEGORI</th>
                    <th class="text-end">REALISASI</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($budgetPlan->items as $item)
                    <tr>
                      <td>{{ $item->project->name ?? '-' }}</td>
                      <td>{{ $item->chartAccount ? $item->chartAccount->code . ' - ' . $item->chartAccount->name : '-' }}</td>
                      <td>
                        @if ($item->bankAccount)
                          {{ $item->bankAccount->bank_name }} - {{ $item->bankAccount->account_number }}
                        @else
                          -
                        @endif
                      </td>
                      <td>{{ $item->item_name }}</td>
                      <td>{{ $item->vendor_name ?? '-' }}</td>
                      <td>
                        <select class="form-select" name="items[{{ $item->id }}][category]" required>
                          <option value="">-- Pilih --</option>
                          @foreach ($categories as $category)
                            <option value="{{ $category->name }}" @selected(old('items.' . $item->id . '.category', $item->category) === $category->name)>
                              {{ $category->name }}
                            </option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input class="form-control text-end" name="items[{{ $item->id }}][real_amount]" type="number" step="0.01" min="0" value="{{ old('items.' . $item->id . '.real_amount', $item->real_amount) }}" required>
                      </td>
                    </tr>
                  @empty
                <tr>
                  <td colspan="7" class="text-center">Belum ada item.</td>
                </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <div class="text-end">
              <button class="btn btn-primary" type="submit">Simpan Real Expense</button>
            </div>
          </form>
        </div>
      </div>
    @endcan

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
