@extends('layouts.app')

@section('title', 'Detail — ' . $taskProject->name)

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>{{ $taskProject->name }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.index') }}">Task Projects</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Action buttons ─────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5>{{ $taskProject->name }}</h5>
            <div class="d-flex gap-2 flex-wrap">
                @can('view', $taskProject)
                    <a class="btn btn-secondary" href="{{ route('task-projects.tasks.index', $taskProject) }}">
                        <i data-feather="check-square" style="width:15px;height:15px;"></i> Tasks
                    </a>
                    <a class="btn btn-info" href="{{ route('task-projects.kanban', $taskProject) }}">
                        <i data-feather="trello" style="width:15px;height:15px;"></i> Kanban
                    </a>
                @endcan
                @can('update', $taskProject)
                    <a class="btn btn-primary" href="{{ route('task-projects.edit', $taskProject) }}">
                        <i data-feather="edit-2" style="width:15px;height:15px;"></i> Edit
                    </a>
                @endcan
                @can('delete', $taskProject)
                    <form method="post" action="{{ route('task-projects.destroy', $taskProject) }}"
                          onsubmit="return confirm('Hapus project ini?')">
                        @csrf
                        @method('delete')
                        <button class="btn btn-danger" type="submit">
                            <i data-feather="trash-2" style="width:15px;height:15px;"></i> Hapus
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="card-body">
            {{-- ── Project info ──────────────────────────────────── --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="text-muted small">Nama Project</div>
                    <div class="fw-bold">{{ $taskProject->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Project Manager</div>
                    <div class="fw-bold">{{ $taskProject->projectManager?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Dibuat oleh</div>
                    <div class="fw-bold">{{ $taskProject->creator?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Tanggal Dibuat</div>
                    <div class="fw-bold">{{ $taskProject->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
            </div>

            <hr>

            {{-- ── Summary cards ─────────────────────────────────── --}}
            <h6 class="mb-3 text-muted">Ringkasan Project</h6>
            <div class="row g-3 mb-4">
                {{-- Status turunan --}}
                <div class="col-md-3 col-6">
                    <div class="card border shadow-none text-center p-3">
                        <div class="text-muted small mb-1">Status Project</div>
                        @php
                            $statusBadge = [
                                'not_started' => 'secondary',
                                'on_track'    => 'primary',
                                'blocked'     => 'danger',
                                'done'        => 'success',
                            ];
                            $statusLabel = [
                                'not_started' => 'Belum Mulai',
                                'on_track'    => 'On Track',
                                'blocked'     => 'Blocked',
                                'done'        => 'Selesai',
                            ];
                            $st = $summary['project_status'];
                        @endphp
                        <span class="badge badge-light-{{ $statusBadge[$st] ?? 'secondary' }} fs-6">
                            {{ $statusLabel[$st] ?? $st }}
                        </span>
                    </div>
                </div>

                {{-- Progress percent --}}
                <div class="col-md-3 col-6">
                    <div class="card border shadow-none text-center p-3">
                        <div class="text-muted small mb-1">Progress</div>
                        <div class="fw-bold fs-4 text-{{ $summary['progress_percent'] === 100 ? 'success' : 'primary' }}">
                            {{ $summary['progress_percent'] }}%
                        </div>
                        <div class="progress mt-2" style="height:6px;">
                            <div class="progress-bar bg-{{ $summary['progress_percent'] === 100 ? 'success' : 'primary' }}"
                                 style="width:{{ $summary['progress_percent'] }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Total tasks --}}
                <div class="col-md-3 col-6">
                    <div class="card border shadow-none text-center p-3">
                        <div class="text-muted small mb-1">Total Task</div>
                        <div class="fw-bold fs-4">{{ $summary['total_tasks'] }}</div>
                    </div>
                </div>

                {{-- Per-status breakdown --}}
                <div class="col-md-3 col-6">
                    <div class="card border shadow-none p-3">
                        <div class="text-muted small mb-2">Per Status</div>
                        <div class="d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-light-secondary">To Do</span>
                                <strong>{{ $summary['todo'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-light-primary">In Progress</span>
                                <strong>{{ $summary['doing'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-light-danger">Blocked</span>
                                <strong>{{ $summary['blocked'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="badge badge-light-success">Done</span>
                                <strong>{{ $summary['done'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
