@extends('layouts.app')

@section('title', 'Rekap Project')

@php
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 2, ',', '.');
@endphp

@section('content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Rekap Project</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Rekap Project</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header pb-0">
                <h5>List Rekap Project</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Rincian Kontrak</th>
                                <th>Balance Due</th>
                                <th>Hak Perusahaan (20%)</th>
                                <th>FeeSeles (1,5%)</th>
                                <th>Modal Kerja (78,5%)</th>
                                <th>HPP (Realisasi per Akun)</th>
                                <th>Disconto</th>
                                <th>BALANCE DUE Project</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php
                                    $hppRows = $hppByProject->get($project->id, collect());
                                    $hppTotal = (float) $hppRows->sum('total_amount');
                                    $workingCapitalAmount = $project->working_capital_amount;
                                    $discontoAmount = $project->disconto_amount;
                                    $balanceDueProjectAmount =
                                        $workingCapitalAmount !== null && $discontoAmount !== null
                                            ? round((float) $workingCapitalAmount - $hppTotal - (float) $discontoAmount, 2)
                                            : null;
                                    $workingCapitalToHppPercentage =
                                        $workingCapitalAmount !== null && (float) $workingCapitalAmount > 0
                                            ? round(($hppTotal / (float) $workingCapitalAmount) * 100, 2)
                                            : null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $project->name }}</div>
                                        <small class="text-muted">{{ $project->company->name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div>Nilai Kontrak SPK:
                                            <span class="fw-bold">
                                                {{ $project->contract_value !== null ? $formatRupiah($project->contract_value) : '-' }}
                                            </span>
                                        </div>
                                        <div>PPH:
                                            <span class="fw-bold">
                                                {{ $project->pph_amount !== null ? $formatRupiah($project->pph_amount) : '-' }}
                                            </span>
                                        </div>
                                        <div>PPN:
                                            <span class="fw-bold">
                                                {{ $project->ppn_amount !== null ? $formatRupiah($project->ppn_amount) : '-' }}
                                            </span>
                                        </div>
                                        <div>Total Kontrak Net:
                                            <span class="fw-bold">
                                                {{ $project->net_contract_value !== null ? $formatRupiah($project->net_contract_value) : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>Dana Masuk:
                                            <span class="fw-bold">{{ $formatRupiah($project->total_received_amount) }}</span>
                                        </div>
                                        <div>Outstanding:
                                            <span class="fw-bold">
                                                {{ $project->outstanding_contract_value !== null ? $formatRupiah($project->outstanding_contract_value) : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ $formatRupiah($project->company_right_amount) }}</td>
                                    <td class="text-end">{{ $formatRupiah($project->fee_seles_amount) }}</td>
                                    <td class="text-end">
                                        {{ $project->working_capital_amount !== null ? $formatRupiah($project->working_capital_amount) : '-' }}
                                    </td>
                                    <td>
                                        @if ($hppRows->isEmpty())
                                            <span class="text-muted">Belum ada realisasi</span>
                                        @else
                                            @foreach ($hppRows as $hpp)
                                                <div>
                                                    {{ $hpp->code }} - {{ $hpp->name }}:
                                                    <span class="fw-bold">{{ $formatRupiah($hpp->total_amount) }}</span>
                                                </div>
                                            @endforeach
                                            <div class="mt-2 border-top pt-2">
                                                <span class="text-muted">Total HPP:</span>
                                                <span class="fw-bold">{{ $formatRupiah($hppTotal) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted">Prosentase Modal Kerja / HPP:</span>
                                                <span class="fw-bold">
                                                    {{ $workingCapitalToHppPercentage !== null ? number_format($workingCapitalToHppPercentage, 2, ',', '.') . '%' : '-' }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($discontoAmount !== null)
                                            {{ $formatRupiah($discontoAmount) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ $balanceDueProjectAmount !== null ? $formatRupiah($balanceDueProjectAmount) : '-' }}
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-light"
                                            href="{{ route('projects.show', $project) }}">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">Belum ada project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
