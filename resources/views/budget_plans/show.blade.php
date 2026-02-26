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

  $submissionDate = $budgetPlan->submission_date;
  $weekOfMonthDisplay = $budgetPlan->week_of_month;
  if ($weekOfMonthDisplay === null && $submissionDate) {
      $firstOfMonth = $submissionDate->copy()->startOfMonth();
      $offset = $firstOfMonth->dayOfWeekIso - 1;
      $weekOfMonthDisplay = intdiv($offset + ($submissionDate->day - 1), 7) + 1;
  }
  $projectCountDisplay = $budgetPlan->project_count ?? $budgetPlan->items->pluck('project_id')->filter()->unique()->count();
  $itemRealizedMap = collect($itemRealizedMap ?? []);
  $realizations = $realizations ?? collect();
  $vendorsByProject = $vendorsByProject ?? collect();
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
                @php
                  $realizedAmount = (float) ($itemRealizedMap->get($item->id) ?? 0);
                  $lineTotal = (float) ($item->line_total ?? 0);
                  $overBudgetAmount = max(0, round($realizedAmount - $lineTotal, 2));
                @endphp
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
                  <td class="text-end">
                    {{ number_format($realizedAmount, 2, ',', '.') }}
                    @if ($overBudgetAmount > 0)
                      <div class="mt-1">
                        <span class="badge badge-light-danger">Over Budget</span>
                        <small class="text-danger d-block">Selisih: Rp {{ number_format($overBudgetAmount, 2, ',', '.') }}</small>
                      </div>
                    @endif
                  </td>
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

    @if ($budgetPlan->status === 'approved' || $realizations->isNotEmpty())
      @php
        $realisableItems = $budgetPlan->items->filter(fn($item) => $item->project_id && $item->chart_account_id)->values();
        $defaultItem = $realisableItems->first();
        $defaultProjectVendors = $defaultItem ? ($vendorsByProject->get($defaultItem->project_id) ?? collect()) : collect();
      @endphp
      <div class="card">
        <div class="card-header pb-0">
          <h5>Realisasi Budget Plan</h5>
        </div>
        <div class="card-body">
          @can('manageRealization', $budgetPlan)
            <div class="border rounded p-3 mb-3">
              <h6 class="mb-3">Input Realisasi</h6>
              <form method="post" action="{{ route('budget-plans.realizations.store', $budgetPlan) }}" class="js-realization-form" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                  <div class="col-md-7">
                    <label class="form-label">Item BP</label>
                    <select class="form-select" id="realization-item-id" name="budget_plan_item_id" required>
                      @if ($realisableItems->isEmpty())
                        <option value="">Tidak ada item BP yang bisa direalisasikan</option>
                      @else
                        @foreach ($realisableItems as $item)
                          @php
                            $itemRealizedAmount = (float) ($itemRealizedMap->get($item->id) ?? 0);
                            $itemRemaining = round((float) $item->line_total - $itemRealizedAmount, 2);
                          @endphp
                          <option value="{{ $item->id }}"
                            data-project-id="{{ $item->project_id }}"
                            {{ $defaultItem && $defaultItem->id === $item->id ? 'selected' : '' }}>
                            {{ $item->project->name ?? '-' }} | {{ $item->chartAccount ? $item->chartAccount->code . ' - ' . $item->chartAccount->name : '-' }} | {{ $item->item_name }} | Budget Rp {{ number_format($item->line_total, 2, ',', '.') }} | Realisasi Rp {{ number_format($itemRealizedAmount, 2, ',', '.') }} | Sisa Rp {{ number_format($itemRemaining, 2, ',', '.') }}
                          </option>
                        @endforeach
                      @endif
                    </select>
                    @if ($realisableItems->isEmpty())
                      <small class="text-danger d-block mt-1">Item BP harus memiliki project dan akun agar bisa direalisasikan.</small>
                    @endif
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input class="form-control" name="expense_date" type="date" value="{{ now()->format('Y-m-d') }}" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Cari Vendor</label>
                    <input class="form-control" id="realization-vendor-search" type="text" placeholder="Cari vendor existing">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Vendor Existing</label>
                    <select class="form-select" id="realization-vendor-id" name="vendor_id">
                      <option value="">-- Pilih Vendor --</option>
                      @foreach ($defaultProjectVendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Vendor Baru (Opsional)</label>
                    <input class="form-control" name="vendor_new_name" type="text" placeholder="Isi jika vendor belum ada">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">QTY</label>
                    <input class="form-control text-end js-realization-qty" name="quantity" type="number" step="0.01" min="0.01" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Harga Satuan</label>
                    <input class="form-control text-end js-realization-unit-price" name="unit_price" type="number" step="0.01" min="0" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Satuan</label>
                    <input class="form-control" name="unit" type="text" maxlength="50" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Jumlah</label>
                    <input class="form-control text-end js-realization-amount" type="number" step="0.01" readonly>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Catatan</label>
                    <input class="form-control" name="notes" type="text">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Bukti Nota <span class="text-muted">(opsional, pdf/jpg/jpeg/png, maks 5MB)</span></label>
                    <input class="form-control" name="invoice_proof_file" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    @error('invoice_proof_file') <small class="text-danger">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Mutasi Rekening <span class="text-muted">(opsional, pdf/jpg/jpeg/png, maks 5MB)</span></label>
                    <input class="form-control" name="bank_mutation_file" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    @error('bank_mutation_file') <small class="text-danger">{{ $message }}</small> @enderror
                  </div>
                  <div class="col-md-12 text-end">
                    <button class="btn btn-primary" type="submit" @disabled($realisableItems->isEmpty())>Simpan Realisasi</button>
                  </div>
                </div>
              </form>
            </div>
          @endcan

          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Project</th>
                  <th>Item BP</th>
                  <th>Akun</th>
                  <th>Vendor</th>
                  <th class="text-end">QTY</th>
                  <th>Satuan</th>
                  <th class="text-end">Harga Satuan</th>
                  <th class="text-end">Jumlah</th>
                  <th>Catatan</th>
                  <th>Bukti Nota</th>
                  <th>Mutasi Rekening</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($realizations as $realization)
                  @php
                    $realizationVendors = $vendorsByProject->get($realization->project_id) ?? collect();
                  @endphp
                  <tr>
                    <td>{{ $realization->expense_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $realization->project->name ?? '-' }}</td>
                    <td>{{ $realization->budgetPlanItem?->item_name ?? '-' }}</td>
                    <td>{{ $realization->chartAccount ? $realization->chartAccount->code . ' - ' . $realization->chartAccount->name : '-' }}</td>
                    <td>{{ $realization->vendor->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($realization->quantity, 2, ',', '.') }}</td>
                    <td>{{ $realization->unit }}</td>
                    <td class="text-end">{{ number_format($realization->unit_price, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($realization->amount, 2, ',', '.') }}</td>
                    <td>{{ $realization->notes ?: '-' }}</td>
                    <td>
                      @if ($realization->invoice_proof_path)
                        <a href="{{ route('budget-plans.realizations.invoice-proof', [$budgetPlan, $realization]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Unduh</a>
                        <small class="d-block text-muted">{{ $realization->invoice_proof_original_name }}</small>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if ($realization->bank_mutation_path)
                        <a href="{{ route('budget-plans.realizations.bank-mutation', [$budgetPlan, $realization]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Unduh</a>
                        <small class="d-block text-muted">{{ $realization->bank_mutation_original_name }}</small>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td class="text-end">
                      @can('manageRealization', $budgetPlan)
                        <details class="d-inline-block">
                          <summary class="btn btn-sm btn-primary d-inline-block">Edit</summary>
                          <div class="mt-2 p-2 border rounded bg-light text-start" style="min-width: 500px;">
                            <form method="post" action="{{ route('budget-plans.realizations.update', [$budgetPlan, $realization]) }}" class="js-realization-form" enctype="multipart/form-data">
                              @csrf
                              @method('put')
                              <div class="row g-2">
                                <div class="col-md-6">
                                  <label class="form-label mb-1">Project / Item (Locked)</label>
                                  <input class="form-control form-control-sm" type="text"
                                    value="{{ $realization->project->name ?? '-' }} - {{ $realization->item_name }}" readonly>
                                </div>
                                <div class="col-md-3">
                                  <label class="form-label mb-1">Tanggal</label>
                                  <input class="form-control form-control-sm" name="expense_date" type="date"
                                    value="{{ $realization->expense_date?->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-3">
                                  <label class="form-label mb-1">Vendor Existing</label>
                                  <select class="form-select form-select-sm" name="vendor_id">
                                    <option value="">-- Pilih Vendor --</option>
                                    @foreach ($realizationVendors as $vendor)
                                      <option value="{{ $vendor->id }}" @selected((int) $realization->vendor_id === (int) $vendor->id)>{{ $vendor->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label mb-1">Vendor Baru (Opsional)</label>
                                  <input class="form-control form-control-sm" name="vendor_new_name" type="text" placeholder="Isi jika vendor baru">
                                </div>
                                <div class="col-md-2">
                                  <label class="form-label mb-1">QTY</label>
                                  <input class="form-control form-control-sm text-end js-realization-qty" name="quantity" type="number"
                                    step="0.01" min="0.01" value="{{ number_format((float) $realization->quantity, 2, '.', '') }}" required>
                                </div>
                                <div class="col-md-2">
                                  <label class="form-label mb-1">Harga Satuan</label>
                                  <input class="form-control form-control-sm text-end js-realization-unit-price" name="unit_price" type="number"
                                    step="0.01" min="0" value="{{ number_format((float) $realization->unit_price, 2, '.', '') }}" required>
                                </div>
                                <div class="col-md-2">
                                  <label class="form-label mb-1">Satuan</label>
                                  <input class="form-control form-control-sm" name="unit" type="text"
                                    value="{{ $realization->unit }}" required>
                                </div>
                                <div class="col-md-2">
                                  <label class="form-label mb-1">Jumlah</label>
                                  <input class="form-control form-control-sm text-end js-realization-amount" type="number"
                                    step="0.01" value="{{ number_format((float) $realization->amount, 2, '.', '') }}" readonly>
                                </div>
                                <div class="col-md-12">
                                  <label class="form-label mb-1">Catatan</label>
                                  <input class="form-control form-control-sm" name="notes" type="text"
                                    value="{{ $realization->notes }}">
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label mb-1">Bukti Nota baru <span class="text-muted">(pdf/jpg/jpeg/png, maks 5MB)</span></label>
                                  <input class="form-control form-control-sm" name="invoice_proof_file" type="file" accept=".pdf,.jpg,.jpeg,.png">
                                  @if ($realization->invoice_proof_path)
                                    <small class="text-success d-block mt-1">File ada: {{ $realization->invoice_proof_original_name }}</small>
                                  @endif
                                  @error('invoice_proof_file') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label mb-1">Mutasi Rekening baru <span class="text-muted">(pdf/jpg/jpeg/png, maks 5MB)</span></label>
                                  <input class="form-control form-control-sm" name="bank_mutation_file" type="file" accept=".pdf,.jpg,.jpeg,.png">
                                  @if ($realization->bank_mutation_path)
                                    <small class="text-success d-block mt-1">File ada: {{ $realization->bank_mutation_original_name }}</small>
                                  @endif
                                  @error('bank_mutation_file') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-12 text-end">
                                  <button class="btn btn-sm btn-primary" type="submit">Simpan</button>
                                </div>
                              </div>
                            </form>
                          </div>
                        </details>
                        <form class="d-inline" method="post" action="{{ route('budget-plans.realizations.destroy', [$budgetPlan, $realization]) }}"
                          onsubmit="return confirm('Hapus transaksi realisasi ini?')">
                          @csrf
                          @method('delete')
                          <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                        </form>
                      @else
                        -
                      @endcan
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="13" class="text-center">Belum ada transaksi realisasi.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

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

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const vendorsByProject = @json(
        $vendorsByProject
          ->map(fn ($vendors) => $vendors->map(fn ($vendor) => ['id' => $vendor->id, 'name' => $vendor->name])->values())
          ->toArray()
      );

      const realizationItemSelect = document.getElementById('realization-item-id');
      const realizationVendorSelect = document.getElementById('realization-vendor-id');
      const realizationVendorSearch = document.getElementById('realization-vendor-search');

      let currentVendors = [];

      const renderVendorOptions = (selectedValue = '', searchTerm = '') => {
        if (!realizationVendorSelect) {
          return;
        }

        const keyword = String(searchTerm || '').trim().toLowerCase();
        const filtered = keyword
          ? currentVendors.filter((vendor) => String(vendor.name).toLowerCase().includes(keyword))
          : currentVendors;

        realizationVendorSelect.innerHTML = '<option value="">-- Pilih Vendor --</option>';

        filtered.forEach((vendor) => {
          const option = document.createElement('option');
          option.value = String(vendor.id);
          option.textContent = vendor.name;
          if (selectedValue && String(vendor.id) === String(selectedValue)) {
            option.selected = true;
          }
          realizationVendorSelect.appendChild(option);
        });
      };

      const syncVendorBySelectedItem = () => {
        if (!realizationItemSelect) {
          return;
        }

        const selectedOption = realizationItemSelect.options[realizationItemSelect.selectedIndex];
        const projectId = selectedOption ? selectedOption.dataset.projectId : null;
        currentVendors = projectId && vendorsByProject[projectId] ? vendorsByProject[projectId] : [];
        renderVendorOptions('', realizationVendorSearch ? realizationVendorSearch.value : '');
      };

      if (realizationItemSelect && realizationVendorSelect) {
        syncVendorBySelectedItem();
        realizationItemSelect.addEventListener('change', syncVendorBySelectedItem);
      }

      if (realizationVendorSearch) {
        realizationVendorSearch.addEventListener('input', function () {
          renderVendorOptions(realizationVendorSelect ? realizationVendorSelect.value : '', realizationVendorSearch.value);
        });
      }

      const bindRealizationFormula = (formElement) => {
        const qtyInput = formElement.querySelector('.js-realization-qty');
        const unitPriceInput = formElement.querySelector('.js-realization-unit-price');
        const amountOutput = formElement.querySelector('.js-realization-amount');

        if (!qtyInput || !unitPriceInput || !amountOutput) {
          return;
        }

        const recalc = () => {
          const qty = Number(qtyInput.value || 0);
          const unitPrice = Number(unitPriceInput.value || 0);
          const safeQty = Number.isFinite(qty) && qty > 0 ? qty : 0;
          const safeUnitPrice = Number.isFinite(unitPrice) && unitPrice > 0 ? unitPrice : 0;
          const amount = Math.round((safeQty * safeUnitPrice + Number.EPSILON) * 100) / 100;
          amountOutput.value = amount.toFixed(2);
        };

        qtyInput.addEventListener('input', recalc);
        unitPriceInput.addEventListener('input', recalc);
        recalc();
      };

      document.querySelectorAll('.js-realization-form').forEach((formElement) => {
        bindRealizationFormula(formElement);
      });
    });
  </script>
@endpush
