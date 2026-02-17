@extends('layouts.app')

@section('title', 'Detail Master Pajak')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Detail Master Pajak</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tax-masters.index') }}">Master Pajak</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>{{ strtoupper($taxMaster->tax_type) }} - {{ $taxMaster->name }}</h5>
        <div class="d-flex gap-2">
          <a class="btn btn-primary" href="{{ route('tax-masters.edit', $taxMaster) }}">Edit</a>
          <form method="post" action="{{ route('tax-masters.destroy', $taxMaster) }}" onsubmit="return confirm('Hapus master pajak ini?')">
            @csrf
            @method('delete')
            <button class="btn btn-danger" type="submit">Hapus</button>
          </form>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted">Jenis Pajak</div>
            <div class="fw-bold">{{ strtoupper($taxMaster->tax_type) }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Nama Tarif</div>
            <div class="fw-bold">{{ $taxMaster->name }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Persentase</div>
            <div class="fw-bold">{{ number_format($taxMaster->percentage, 2, ',', '.') }}%</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Status</div>
            <div class="fw-bold">{{ $taxMaster->is_active ? 'Aktif' : 'Nonaktif' }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Dibuat</div>
            <div class="fw-bold">{{ $taxMaster->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Diperbarui</div>
            <div class="fw-bold">{{ $taxMaster->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
