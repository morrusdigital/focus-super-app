@extends('layouts.app')

@section('title', 'Detail Kategori BP')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Detail Kategori BP</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('budget-plan-categories.index') }}">Kategori BP</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>{{ $category->name }}</h5>
        <div class="d-flex gap-2">
          <a class="btn btn-primary" href="{{ route('budget-plan-categories.edit', $category) }}">Edit</a>
          <form method="post" action="{{ route('budget-plan-categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
            @csrf
            @method('delete')
            <button class="btn btn-danger" type="submit">Hapus</button>
          </form>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted">Nama</div>
            <div class="fw-bold">{{ $category->name }}</div>
          </div>
          <div class="col-md-6">
            <div class="text-muted">Status</div>
            <div class="fw-bold">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
