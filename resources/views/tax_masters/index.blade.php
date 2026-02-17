@extends('layouts.app')

@section('title', 'Master Pajak')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Master Pajak</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Master Pajak</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Daftar Master Pajak</h5>
        <a class="btn btn-primary" href="{{ route('tax-masters.create') }}">Tambah Pajak</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>Jenis</th>
                <th>Nama Tarif</th>
                <th class="text-end">Persentase</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($taxMasters as $taxMaster)
                <tr>
                  <td>{{ strtoupper($taxMaster->tax_type) }}</td>
                  <td>{{ $taxMaster->name }}</td>
                  <td class="text-end">{{ number_format($taxMaster->percentage, 2, ',', '.') }}%</td>
                  <td>{{ $taxMaster->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-light" href="{{ route('tax-masters.show', $taxMaster) }}">Detail</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('tax-masters.edit', $taxMaster) }}">Edit</a>
                    <form class="d-inline" method="post" action="{{ route('tax-masters.destroy', $taxMaster) }}" onsubmit="return confirm('Hapus master pajak ini?')">
                      @csrf
                      @method('delete')
                      <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center">Belum ada master pajak.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
