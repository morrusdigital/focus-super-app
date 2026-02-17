@extends('layouts.app')

@section('title', 'Rekening Perusahaan')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Rekening Perusahaan</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Rekening</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Daftar Rekening</h5>
        <a class="btn btn-primary" href="{{ route('bank-accounts.create') }}">Tambah Rekening</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>Bank</th>
                <th>No. Rekening</th>
                <th>Atas Nama</th>
                <th>Utama</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($bankAccounts as $bankAccount)
                <tr>
                  <td>{{ $bankAccount->bank_name }}</td>
                  <td>{{ $bankAccount->account_number }}</td>
                  <td>{{ $bankAccount->account_name }}</td>
                  <td>{{ $bankAccount->is_default ? 'Ya' : '-' }}</td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-light" href="{{ route('bank-accounts.show', $bankAccount) }}">Detail</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('bank-accounts.edit', $bankAccount) }}">Edit</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Belum ada rekening.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
