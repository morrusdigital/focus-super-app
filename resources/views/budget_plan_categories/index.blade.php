@extends('layouts.app')

@section('title', 'Kategori BP')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Kategori BP</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Kategori BP</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Daftar Kategori</h5>
        <a class="btn btn-primary" href="{{ route('budget-plan-categories.create') }}">Tambah Kategori</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($categories as $category)
                <tr>
                  <td>{{ $category->name }}</td>
                  <td>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-light" href="{{ route('budget-plan-categories.show', $category) }}">Detail</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('budget-plan-categories.edit', $category) }}">Edit</a>
                    <form class="d-inline" method="post" action="{{ route('budget-plan-categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                      @csrf
                      @method('delete')
                      <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center">Belum ada kategori.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
