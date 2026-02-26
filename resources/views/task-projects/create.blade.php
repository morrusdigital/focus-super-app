@extends('layouts.app')

@section('title', 'Buat Task Project')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Buat Task Project</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.index') }}">Task Projects</a></li>
                    <li class="breadcrumb-item active">Buat</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0">
            <h5>Form Task Project Baru</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('task-projects.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror"
                               name="name" type="text" value="{{ old('name') }}"
                               required maxlength="255" placeholder="Masukkan nama project">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Project Manager <span class="text-danger">*</span></label>
                        <select class="form-select @error('project_manager_id') is-invalid @enderror"
                                name="project_manager_id" required>
                            <option value="">-- Pilih Project Manager --</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}"
                                    {{ old('project_manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->role }})
                                </option>
                            @endforeach
                        </select>
                        @error('project_manager_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Simpan</button>
                    <a class="btn btn-secondary" href="{{ route('task-projects.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
