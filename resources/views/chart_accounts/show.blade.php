@extends('layouts.app')

@section('title', 'Detail Akun')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Detail Akun</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chart-accounts.index') }}">Akun</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>{{ $account->code }} - {{ $account->name }}</h5>
        <div class="d-flex gap-2">
          <a class="btn btn-primary" href="{{ route('chart-accounts.edit', $account) }}">Edit</a>
          <form method="post" action="{{ route('chart-accounts.destroy', $account) }}" onsubmit="return confirm('Hapus akun ini?')">
            @csrf
            @method('delete')
            <button class="btn btn-danger" type="submit">Hapus</button>
          </form>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted">Kode</div>
            <div class="fw-bold">{{ $account->code }}</div>
          </div>
          <div class="col-md-6">
            <div class="text-muted">Nama</div>
            <div class="fw-bold">{{ $account->name }}</div>
          </div>
          <div class="col-md-2">
            <div class="text-muted">Status</div>
            <div class="fw-bold">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
