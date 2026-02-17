@extends('layouts.app')

@section('title', 'Akun')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Akun</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Akun</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Daftar Akun</h5>
        <a class="btn btn-primary" href="{{ route('chart-accounts.create') }}">Tambah Akun</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($accounts as $account)
                <tr>
                  <td>{{ $account->code }}</td>
                  <td>{{ $account->name }}</td>
                  <td>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-light" href="{{ route('chart-accounts.show', $account) }}">Detail</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('chart-accounts.edit', $account) }}">Edit</a>
                    <form class="d-inline" method="post" action="{{ route('chart-accounts.destroy', $account) }}" onsubmit="return confirm('Hapus akun ini?')">
                      @csrf
                      @method('delete')
                      <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center">Belum ada akun.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
