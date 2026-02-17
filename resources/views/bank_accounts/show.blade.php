@extends('layouts.app')

@section('title', 'Detail Rekening')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Detail Rekening</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bank-accounts.index') }}">Rekening</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>{{ $bankAccount->bank_name }}</h5>
        <div class="d-flex gap-2">
          <a class="btn btn-primary" href="{{ route('bank-accounts.edit', $bankAccount) }}">Edit</a>
          <form method="post" action="{{ route('bank-accounts.destroy', $bankAccount) }}" onsubmit="return confirm('Hapus rekening ini?')">
            @csrf
            @method('delete')
            <button class="btn btn-danger" type="submit">Hapus</button>
          </form>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted">Bank</div>
            <div class="fw-bold">{{ $bankAccount->bank_name }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">No. Rekening</div>
            <div class="fw-bold">{{ $bankAccount->account_number }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Atas Nama</div>
            <div class="fw-bold">{{ $bankAccount->account_name }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Rekening Utama</div>
            <div class="fw-bold">{{ $bankAccount->is_default ? 'Ya' : '-' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
