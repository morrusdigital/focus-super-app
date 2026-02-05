@extends('layouts.app')

@section('title', 'Tambah Project')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Tambah Project</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Tambah</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="card">
          <div class="card-header pb-0">
            <h5>Form Tambah Project</h5>
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('projects.store') }}">
              @csrf

              <div class="mb-3">
                <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       name="name" value="{{ old('name') }}" required>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          name="description" rows="4">{{ old('description') }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Project Manager <span class="text-danger">*</span></label>
                  <select class="form-select @error('manager_id') is-invalid @enderror"
                          name="manager_id" required>
                    <option value="">-- Pilih Manager --</option>
                    @foreach($managers as $manager)
                      <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('manager_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Status <span class="text-danger">*</span></label>
                  <select class="form-select @error('status') is-invalid @enderror"
                          name="status" required>
                    <option value="planning" {{ old('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                  </select>
                  @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tanggal Mulai</label>
                  <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                         name="start_date" value="{{ old('start_date') }}">
                  @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Tanggal Selesai</label>
                  <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                         name="end_date" value="{{ old('end_date') }}">
                  @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Budget (Rp)</label>
                <input type="number" class="form-control @error('budget') is-invalid @enderror"
                       name="budget" value="{{ old('budget') }}" min="0" step="1000">
                @error('budget')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Opsional, budget untuk project ini</small>
              </div>

              <div class="alert alert-light-info">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Info:</strong> Kanban board dengan 7 kolom dan 5 milestone default akan otomatis dibuat setelah project berhasil disimpan.
              </div>

              <div class="d-flex justify-content-between">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                  <i class="fa fa-arrow-left me-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-save me-1"></i> Simpan Project
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
