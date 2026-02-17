@extends('layouts.app')

@section('title', 'Detail Project')

@section('content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Detail Project</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                <h5>{{ $project->name }}</h5>
                <div class="d-flex gap-2">
                    @can('update', $project)
                        <a class="btn btn-primary" href="{{ route('projects.edit', $project) }}">Edit</a>
                    @endcan
                    @can('delete', $project)
                        <form method="post" action="{{ route('projects.destroy', $project) }}"
                            onsubmit="return confirm('Hapus project ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-danger" type="submit">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if (!$project->isTaxConfigurationComplete())
                    <div class="alert alert-warning">Data project belum lengkap. Mohon lengkapi alamat, nilai kontrak, dan
                        pengaturan pajak.</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted">Nama Project</div>
                        <div class="fw-bold">{{ $project->name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Alamat Project</div>
                        <div class="fw-bold">{{ $project->address ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Nilai Kontrak SPK</div>
                        <div class="fw-bold">
                            @if ($project->contract_value !== null)
                                Rp {{ number_format($project->contract_value, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Total Kontrak Net</div>
                        <div class="fw-bold">
                            @if ($project->net_contract_value !== null)
                                Rp {{ number_format($project->net_contract_value, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </div>
                        <small class="text-muted d-block mt-1">
                            Nominal PPH:
                            @if ($project->pph_amount !== null)
                                Rp {{ number_format($project->pph_amount, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </small>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Total Termin</div>
                        <div class="fw-bold">Rp {{ number_format($project->total_term_amount, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Total Dana Masuk</div>
                        <div class="fw-bold">Rp {{ number_format($project->total_received_amount, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Unapplied Balance</div>
                        <div class="fw-bold">Rp {{ number_format($project->unapplied_balance, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Outstanding Total</div>
                        <div class="fw-bold">Rp {{ number_format($project->outstanding_total, 2, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">PPH</div>
                        <div class="fw-bold">
                            @if ($project->use_pph === null)
                                -
                            @elseif (!$project->use_pph)
                                Tidak
                            @else
                                Ya - {{ $project->pphTaxMaster->name ?? '-' }}
                                ({{ $project->pph_rate !== null ? number_format($project->pph_rate, 2, ',', '.') . '%' : '-' }})
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">PPN</div>
                        <div class="fw-bold">
                            @if ($project->use_ppn === null)
                                -
                            @elseif (!$project->use_ppn)
                                Tidak
                            @else
                                Ya - {{ $project->ppnTaxMaster->name ?? '-' }}
                                ({{ $project->ppn_rate !== null ? number_format($project->ppn_rate, 2, ',', '.') . '%' : '-' }})
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Status Data</div>
                        <div class="fw-bold">
                            @if ($project->isTaxConfigurationComplete())
                                <span class="badge badge-light-success">Lengkap</span>
                            @else
                                <span class="badge badge-light-warning">Belum Lengkap</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Dibuat</div>
                        <div class="fw-bold">{{ $project->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted">Diperbarui</div>
                        <div class="fw-bold">{{ $project->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="project-finance-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms-pane"
                            type="button" role="tab">Termin & Invoice</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="receipts-tab" data-bs-toggle="tab" data-bs-target="#receipts-pane"
                            type="button" role="tab">Dana Masuk</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vendors-tab" data-bs-toggle="tab" data-bs-target="#vendors-pane"
                            type="button" role="tab">Master Vendor</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses-pane"
                            type="button" role="tab">Expense Project</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="terms-pane" role="tabpanel">
                        @can('manageTerms', $project)
                            @php
                                $nextSequenceNo = ((int) ($project->terms->max('sequence_no') ?? 0)) + 1;
                            @endphp
                            <div class="border rounded p-3 mb-3">
                                <h6 class="mb-3">Tambah Termin</h6>
                                <p class="text-muted mb-3">Basis prosentase: Nilai Kontrak SPK + PPN (jika PPN dipilih).</p>
                                <form method="post" action="{{ route('projects.terms.store', $project) }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Urutan</label>
                                            <input class="form-control" type="text" value="{{ $nextSequenceNo }}"
                                                readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Persentase</label>
                                            <input class="form-control" name="percentage" type="number" step="0.01"
                                                min="0.01" max="100" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Catatan</label>
                                            <input class="form-control" name="notes" type="text">
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button class="btn btn-primary" type="submit">Simpan Termin</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endcan

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Urutan</th>
                                        <th>Persentase</th>
                                        <th class="text-end">Nilai</th>
                                        <th>Invoice</th>
                                        <th>Status</th>
                                        <th class="text-end">Outstanding</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($project->terms as $term)
                                        <tr>
                                            <td>{{ $term->sequence_no }}</td>
                                            <td>
                                                {{ number_format($term->percentage, 2, ',', '.') }}%
                                                @if ($term->notes)
                                                    <small class="text-muted d-block">{{ $term->notes }}</small>
                                                @endif
                                            </td>
                                            <td class="text-end">Rp {{ number_format($term->amount, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($term->invoice_number)
                                                    {{ $term->invoice_number }}<br>
                                                    <small
                                                        class="text-muted">{{ $term->invoice_date?->format('d/m/Y') }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($term->status === 'draft')
                                                    <span class="badge badge-light-secondary">Draft</span>
                                                @elseif ($term->status === 'sent')
                                                    <span class="badge badge-light-primary">Terkirim</span>
                                                @else
                                                    <span class="badge badge-light-success">Lunas</span>
                                                @endif
                                            </td>
                                            <td class="text-end">Rp
                                                {{ number_format($term->outstanding_amount, 2, ',', '.') }}</td>
                                            <td class="text-end">
                                                @can('manageTerms', $project)
                                                    @if ($term->status === 'draft')
                                                        <form class="d-inline" method="post"
                                                            action="{{ route('project-terms.mark-sent', [$project, $term]) }}">
                                                            @csrf
                                                            <button class="btn btn-sm btn-light" type="submit">Generate
                                                                Invoice</button>
                                                        </form>
                                                    @endif
                                                    @if ($term->status !== 'paid')
                                                        <details class="d-inline-block">
                                                            <summary class="btn btn-sm btn-primary d-inline-block">Edit
                                                            </summary>
                                                            <div class="mt-2 p-2 border rounded bg-light text-start"
                                                                style="min-width: 320px;">
                                                                <form method="post"
                                                                    action="{{ route('projects.terms.update', [$project, $term]) }}">
                                                                    @csrf
                                                                    @method('put')
                                                                    <div class="mb-2">
                                                                        <label class="form-label mb-1">Urutan</label>
                                                                        <input class="form-control form-control-sm"
                                                                            type="text" value="{{ $term->sequence_no }}"
                                                                            readonly>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label mb-1">Persentase</label>
                                                                        <input class="form-control form-control-sm"
                                                                            name="percentage" type="number" step="0.01"
                                                                            min="0.01" max="100"
                                                                            value="{{ $term->percentage }}" required>
                                                                        <small class="text-muted d-block mt-1">Basis: Nilai
                                                                            Kontrak SPK + PPN (jika dipilih).</small>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label mb-1">Catatan</label>
                                                                        <input class="form-control form-control-sm"
                                                                            name="notes" type="text"
                                                                            value="{{ $term->notes }}">
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <button class="btn btn-sm btn-primary"
                                                                            type="submit">Simpan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </details>
                                                        <form class="d-inline" method="post"
                                                            action="{{ route('projects.terms.destroy', [$project, $term]) }}"
                                                            onsubmit="return confirm('Hapus termin ini?')">
                                                            @csrf
                                                            @method('delete')
                                                            <button class="btn btn-sm btn-danger"
                                                                type="submit">Hapus</button>
                                                        </form>
                                                    @endif
                                                @else
                                                    -
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Belum ada termin.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="receipts-pane" role="tabpanel">
                        @php
                            $receiptInvoiceTerms = $project->terms
                                ->where('status', 'sent')
                                ->filter(fn($term) => $term->outstanding_amount > 0)
                                ->values();
                            $defaultReceiptTerm = $receiptInvoiceTerms->first();
                            $ppnRateForReceipts = $project->use_ppn ? (float) ($project->ppn_rate ?? 0) : 0.0;
                            $pphRateForReceipts = $project->use_pph ? (float) ($project->pph_rate ?? 0) : 0.0;
                            $defaultReceiptAmount = $defaultReceiptTerm ? (float) $defaultReceiptTerm->outstanding_amount : 0.0;
                            $defaultReceiptDpp = $ppnRateForReceipts > 0
                                ? round($defaultReceiptAmount / (1 + ($ppnRateForReceipts / 100)), 2)
                                : round($defaultReceiptAmount, 2);
                            $defaultReceiptPpn = $ppnRateForReceipts > 0
                                ? round($defaultReceiptAmount - $defaultReceiptDpp, 2)
                                : 0.0;
                            $defaultReceiptPph = $pphRateForReceipts > 0
                                ? round($defaultReceiptDpp * ($pphRateForReceipts / 100), 2)
                                : 0.0;
                            $defaultReceiptNet = round($defaultReceiptDpp - $defaultReceiptPph, 2);
                        @endphp

                        @can('manageReceipts', $project)
                            <div class="border rounded p-3 mb-3">
                                <h6 class="mb-3">Input Dana Masuk</h6>
                                <form method="post" action="{{ route('projects.receipts.store', $project) }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Invoice</label>
                                            <select class="form-select" id="receipt-term-id" name="project_term_id" required>
                                                @if ($receiptInvoiceTerms->isEmpty())
                                                    <option value="">Belum ada invoice aktif</option>
                                                @else
                                                    @foreach ($receiptInvoiceTerms as $term)
                                                        <option value="{{ $term->id }}"
                                                            data-default-amount="{{ (float) $term->outstanding_amount }}"
                                                            {{ $defaultReceiptTerm && $defaultReceiptTerm->id === $term->id ? 'selected' : '' }}>
                                                            Termin {{ $term->sequence_no }} -
                                                            {{ $term->invoice_number ?? 'Belum bernomor' }} |
                                                            Total Rp {{ number_format($term->amount, 2, ',', '.') }} |
                                                            Outstanding Rp
                                                            {{ number_format($term->outstanding_amount, 2, ',', '.') }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if ($receiptInvoiceTerms->isEmpty())
                                                <small class="text-danger d-block mt-1">Buat dan kirim invoice termin terlebih
                                                    dahulu sebelum input dana masuk.</small>
                                            @endif
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Tanggal</label>
                                            <input class="form-control" name="receipt_date" type="date"
                                                value="{{ now()->format('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Nominal</label>
                                            <input class="form-control text-end" id="receipt-amount" name="amount"
                                                type="number" step="0.01" min="0.01"
                                                value="{{ $defaultReceiptTerm ? (float) $defaultReceiptTerm->outstanding_amount : '' }}"
                                                required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">DPP</label>
                                            <input class="form-control text-end" id="receipt-dpp" type="number"
                                                step="0.01" value="{{ $defaultReceiptDpp }}" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">PPH</label>
                                            <input class="form-control text-end" id="receipt-pph" type="number"
                                                step="0.01" value="{{ $defaultReceiptPph }}" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">PPN</label>
                                            <input class="form-control text-end" id="receipt-ppn" type="number"
                                                step="0.01" value="{{ $defaultReceiptPpn }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Dana Masuk Net</label>
                                            <input class="form-control text-end" id="receipt-net" type="number"
                                                step="0.01" value="{{ $defaultReceiptNet }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Sumber</label>
                                            <input class="form-control" name="source" type="text"
                                                placeholder="Transfer/Bank">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">No Referensi</label>
                                            <input class="form-control" name="reference_no" type="text">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Catatan</label>
                                            <input class="form-control" name="notes" type="text">
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button class="btn btn-primary" type="submit"
                                                @disabled($receiptInvoiceTerms->isEmpty())>Simpan Dana Masuk</button>
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
                                        <th class="text-end">Nominal</th>
                                        <th class="text-end">DPP</th>
                                        <th class="text-end">PPH</th>
                                        <th class="text-end">PPN</th>
                                        <th class="text-end">Dana Masuk Net</th>
                                        <th class="text-end">Unapplied</th>
                                        <th>Detail Alokasi</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($project->receipts as $receipt)
                                        @php
                                            $receiptAmount = (float) $receipt->amount;
                                            $receiptDpp = $ppnRateForReceipts > 0
                                                ? round($receiptAmount / (1 + ($ppnRateForReceipts / 100)), 2)
                                                : round($receiptAmount, 2);
                                            $receiptPpn = $ppnRateForReceipts > 0
                                                ? round($receiptAmount - $receiptDpp, 2)
                                                : 0.0;
                                            $receiptPph = $pphRateForReceipts > 0
                                                ? round($receiptDpp * ($pphRateForReceipts / 100), 2)
                                                : 0.0;
                                            $receiptNet = round($receiptDpp - $receiptPph, 2);
                                        @endphp
                                        <tr>
                                            <td>{{ $receipt->receipt_date?->format('d/m/Y') ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($receipt->amount, 2, ',', '.') }}
                                            </td>
                                            <td class="text-end">Rp {{ number_format($receiptDpp, 2, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($receiptPph, 2, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($receiptPpn, 2, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($receiptNet, 2, ',', '.') }}</td>
                                            <td class="text-end">Rp
                                                {{ number_format($receipt->unapplied_amount, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($receipt->allocations->isNotEmpty())
                                                    <ul class="mb-0 ps-3">
                                                        @foreach ($receipt->allocations as $allocation)
                                                            <li>
                                                                Termin {{ $allocation->term?->sequence_no ?? '-' }} -
                                                                Rp {{ number_format($allocation->amount, 2, ',', '.') }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @can('manageReceipts', $project)
                                                    <form class="d-inline" method="post"
                                                        action="{{ route('projects.receipts.destroy', [$project, $receipt]) }}"
                                                        onsubmit="return confirm('Hapus dana masuk ini?')">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            type="submit">Hapus</button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">Belum ada dana masuk.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="vendors-pane" role="tabpanel">
                        @can('manageVendors', $project)
                            <div class="border rounded p-3 mb-3">
                                <h6 class="mb-3">Tambah Vendor Project</h6>
                                <form method="post" action="{{ route('projects.vendors.store', $project) }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-10">
                                            <label class="form-label">Nama Vendor</label>
                                            <input class="form-control" name="name" type="text" required>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end justify-content-end">
                                            <button class="btn btn-primary w-100" type="submit">Simpan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endcan

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Vendor</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($project->vendors as $vendor)
                                        <tr>
                                            <td>{{ $vendor->name }}</td>
                                            <td class="text-end">
                                                @can('manageVendors', $project)
                                                    <details class="d-inline-block">
                                                        <summary class="btn btn-sm btn-primary d-inline-block">Edit</summary>
                                                        <div class="mt-2 p-2 border rounded bg-light text-start"
                                                            style="min-width: 320px;">
                                                            <form method="post"
                                                                action="{{ route('projects.vendors.update', [$project, $vendor]) }}">
                                                                @csrf
                                                                @method('put')
                                                                <div class="mb-2">
                                                                    <label class="form-label mb-1">Nama Vendor</label>
                                                                    <input class="form-control form-control-sm"
                                                                        name="name" type="text"
                                                                        value="{{ $vendor->name }}" required>
                                                                </div>
                                                                <div class="text-end">
                                                                    <button class="btn btn-sm btn-primary"
                                                                        type="submit">Simpan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </details>
                                                    <form class="d-inline" method="post"
                                                        action="{{ route('projects.vendors.destroy', [$project, $vendor]) }}"
                                                        onsubmit="return confirm('Hapus vendor ini?')">
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
                                            <td colspan="2" class="text-center">Belum ada vendor project.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="expenses-pane" role="tabpanel">
                        @php
                            $chartAccounts = $chartAccounts ?? collect();
                        @endphp

                        @can('manageExpenses', $project)
                            <div class="border rounded p-3 mb-3">
                                <h6 class="mb-3">Input Expense Project</h6>
                                <form method="post" action="{{ route('projects.expenses.store', $project) }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Item</label>
                                            <input class="form-control" name="item_name" type="text" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Akun</label>
                                            <select class="form-select" name="chart_account_id" required>
                                                <option value="">-- Pilih Akun --</option>
                                                @foreach ($chartAccounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($chartAccounts->isEmpty())
                                                <small class="text-danger d-block mt-1">Belum ada akun aktif untuk company
                                                    project ini.</small>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Vendor</label>
                                            <select class="form-select" name="vendor_id" required>
                                                <option value="">-- Pilih Vendor --</option>
                                                @foreach ($project->vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($project->vendors->isEmpty())
                                                <small class="text-danger d-block mt-1">Belum ada vendor project. Tambahkan
                                                    dulu di tab Master Vendor.</small>
                                            @endif
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Tanggal Pengeluaran</label>
                                            <input class="form-control" name="expense_date" type="date"
                                                value="{{ now()->format('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Harga Satuan</label>
                                            <input class="form-control text-end js-expense-unit-price" name="unit_price"
                                                type="number" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">QTY</label>
                                            <input class="form-control text-end js-expense-quantity" name="quantity"
                                                type="number" step="0.01" min="0.01" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Satuan</label>
                                            <input class="form-control" name="unit" type="text" maxlength="50"
                                                required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Jumlah</label>
                                            <input class="form-control text-end js-expense-amount" type="number"
                                                step="0.01" readonly>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Keterangan</label>
                                            <input class="form-control" name="notes" type="text">
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button class="btn btn-primary" type="submit"
                                                @disabled($chartAccounts->isEmpty() || $project->vendors->isEmpty())>Simpan Expense</button>
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
                                        <th>Item</th>
                                        <th>Akun</th>
                                        <th>Vendor</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">QTY</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Jumlah</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($project->expenses as $expense)
                                        <tr>
                                            <td>{{ $expense->expense_date?->format('d/m/Y') ?? '-' }}</td>
                                            <td>{{ $expense->item_name }}</td>
                                            <td>{{ $expense->chartAccount ? $expense->chartAccount->code . ' - ' . $expense->chartAccount->name : '-' }}
                                            </td>
                                            <td>{{ $expense->vendor->name ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($expense->unit_price, 2, ',', '.') }}
                                            </td>
                                            <td class="text-end">{{ number_format($expense->quantity, 2, ',', '.') }}</td>
                                            <td>{{ $expense->unit }}</td>
                                            <td class="text-end">Rp {{ number_format($expense->amount, 2, ',', '.') }}</td>
                                            <td>{{ $expense->notes ?: '-' }}</td>
                                            <td class="text-end">
                                                @can('manageExpenses', $project)
                                                    <details class="d-inline-block">
                                                        <summary class="btn btn-sm btn-primary d-inline-block">Edit</summary>
                                                        <div class="mt-2 p-2 border rounded bg-light text-start"
                                                            style="min-width: 460px;">
                                                            <form method="post"
                                                                action="{{ route('projects.expenses.update', [$project, $expense]) }}">
                                                                @csrf
                                                                @method('put')
                                                                <div class="row g-2">
                                                                    <div class="col-md-6">
                                                                        <label class="form-label mb-1">Item</label>
                                                                        <input class="form-control form-control-sm"
                                                                            name="item_name" type="text"
                                                                            value="{{ $expense->item_name }}" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label mb-1">Tanggal</label>
                                                                        <input class="form-control form-control-sm"
                                                                            name="expense_date" type="date"
                                                                            value="{{ $expense->expense_date?->format('Y-m-d') }}"
                                                                            required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label mb-1">Akun</label>
                                                                        <select class="form-select form-select-sm"
                                                                            name="chart_account_id" required>
                                                                            <option value="">-- Pilih Akun --</option>
                                                                            @foreach ($chartAccounts as $account)
                                                                                <option value="{{ $account->id }}"
                                                                                    @selected((int) $expense->chart_account_id === (int) $account->id)>
                                                                                    {{ $account->code }} -
                                                                                    {{ $account->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label mb-1">Vendor</label>
                                                                        <select class="form-select form-select-sm"
                                                                            name="vendor_id" required>
                                                                            <option value="">-- Pilih Vendor --</option>
                                                                            @foreach ($project->vendors as $vendor)
                                                                                <option value="{{ $vendor->id }}"
                                                                                    @selected((int) $expense->vendor_id === (int) $vendor->id)>
                                                                                    {{ $vendor->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label mb-1">Harga Satuan</label>
                                                                        <input
                                                                            class="form-control form-control-sm text-end js-expense-unit-price"
                                                                            name="unit_price" type="number" step="0.01"
                                                                            min="0"
                                                                            value="{{ number_format((float) $expense->unit_price, 2, '.', '') }}"
                                                                            required>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label mb-1">QTY</label>
                                                                        <input
                                                                            class="form-control form-control-sm text-end js-expense-quantity"
                                                                            name="quantity" type="number" step="0.01"
                                                                            min="0.01"
                                                                            value="{{ number_format((float) $expense->quantity, 2, '.', '') }}"
                                                                            required>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label mb-1">Satuan</label>
                                                                        <input class="form-control form-control-sm"
                                                                            name="unit" type="text"
                                                                            value="{{ $expense->unit }}" required>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <label class="form-label mb-1">Jumlah</label>
                                                                        <input
                                                                            class="form-control form-control-sm text-end js-expense-amount"
                                                                            type="number" step="0.01" readonly
                                                                            value="{{ number_format((float) $expense->amount, 2, '.', '') }}">
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <label class="form-label mb-1">Keterangan</label>
                                                                        <input class="form-control form-control-sm"
                                                                            name="notes" type="text"
                                                                            value="{{ $expense->notes }}">
                                                                    </div>
                                                                    <div class="col-md-12 text-end">
                                                                        <button class="btn btn-sm btn-primary"
                                                                            type="submit">Simpan</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </details>
                                                    <form class="d-inline" method="post"
                                                        action="{{ route('projects.expenses.destroy', [$project, $expense]) }}"
                                                        onsubmit="return confirm('Hapus expense ini?')">
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
                                            <td colspan="10" class="text-center">Belum ada expense project.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roundTwo = (value) => Math.round((value + Number.EPSILON) * 100) / 100;

            const invoiceSelect = document.getElementById('receipt-term-id');
            const amountInput = document.getElementById('receipt-amount');
            const dppInput = document.getElementById('receipt-dpp');
            const pphInput = document.getElementById('receipt-pph');
            const ppnInput = document.getElementById('receipt-ppn');
            const netInput = document.getElementById('receipt-net');
            const pphRate = Number(@json($pphRateForReceipts));
            const ppnRate = Number(@json($ppnRateForReceipts));

            if (invoiceSelect && amountInput && dppInput && pphInput && ppnInput && netInput) {
                const syncTaxDetail = () => {
                    const amount = Number(amountInput.value || 0);
                    const validAmount = Number.isFinite(amount) && amount > 0 ? amount : 0;
                    const dpp = ppnRate > 0 ? roundTwo(validAmount / (1 + (ppnRate / 100))) : roundTwo(validAmount);
                    const ppn = ppnRate > 0 ? roundTwo(validAmount - dpp) : 0;
                    const pph = pphRate > 0 ? roundTwo(dpp * (pphRate / 100)) : 0;
                    const net = roundTwo(dpp - pph);

                    dppInput.value = dpp.toFixed(2);
                    ppnInput.value = ppn.toFixed(2);
                    pphInput.value = pph.toFixed(2);
                    netInput.value = net.toFixed(2);
                };

                const syncDefaultAmount = () => {
                    const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
                    if (!selectedOption) {
                        return;
                    }

                    const defaultAmount = selectedOption.dataset.defaultAmount;
                    if (defaultAmount) {
                        amountInput.value = defaultAmount;
                    }

                    syncTaxDetail();
                };

                invoiceSelect.addEventListener('change', syncDefaultAmount);
                amountInput.addEventListener('input', syncTaxDetail);
                syncDefaultAmount();
            }

            const bindExpenseAmountFormula = (container) => {
                const priceInput = container.querySelector('.js-expense-unit-price');
                const qtyInput = container.querySelector('.js-expense-quantity');
                const amountOutput = container.querySelector('.js-expense-amount');

                if (!priceInput || !qtyInput || !amountOutput) {
                    return;
                }

                const recalculateAmount = () => {
                    const unitPrice = Number(priceInput.value || 0);
                    const quantity = Number(qtyInput.value || 0);
                    const safeUnitPrice = Number.isFinite(unitPrice) && unitPrice > 0 ? unitPrice : 0;
                    const safeQuantity = Number.isFinite(quantity) && quantity > 0 ? quantity : 0;
                    const amount = roundTwo(safeUnitPrice * safeQuantity);

                    amountOutput.value = amount.toFixed(2);
                };

                priceInput.addEventListener('input', recalculateAmount);
                qtyInput.addEventListener('input', recalculateAmount);
                recalculateAmount();
            };

            document.querySelectorAll('#expenses-pane form').forEach((form) => bindExpenseAmountFormula(form));
        });
    </script>
@endpush
