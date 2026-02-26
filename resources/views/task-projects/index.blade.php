@extends('layouts.app')

@section('title', 'Task Projects')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Task Projects</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Task Projects</li>
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

    <div class="card">
        <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <h5>Daftar Task Projects</h5>
            @can('create', \App\Models\TaskProject::class)
                <a class="btn btn-primary" href="{{ route('task-projects.create') }}">
                    <i data-feather="plus-circle" style="width:15px;height:15px;"></i> Tambah Project
                </a>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordernone">
                    <thead>
                        <tr>
                            <th>Nama Project</th>
                            <th>Project Manager</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Total Task</th>
                            <th>Dibuat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($taskProjects as $tp)
                            @php $summary = $tp->summary(); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $tp->name }}</td>
                                <td>{{ $tp->projectManager?->name ?? '-' }}</td>
                                <td>
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
                                    <span class="badge badge-light-{{ $statusBadge[$st] ?? 'secondary' }}">
                                        {{ $statusLabel[$st] ?? $st }}
                                    </span>
                                </td>
                                <td style="min-width:100px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress grow" style="height:8px;">
                                            <div class="progress-bar bg-{{ $summary['progress_percent'] === 100 ? 'success' : 'primary' }}"
                                                 role="progressbar"
                                                 style="width:{{ $summary['progress_percent'] }}%"
                                                 aria-valuenow="{{ $summary['progress_percent'] }}"
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-nowrap">{{ $summary['progress_percent'] }}%</small>
                                    </div>
                                </td>
                                <td>{{ $summary['total_tasks'] }}</td>
                                <td>{{ $tp->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end flex-nowrap">
                                        <a class="btn btn-sm btn-light" href="{{ route('task-projects.show', $tp) }}" title="Detail">
                                            <i data-feather="eye" style="width:14px;height:14px;"></i>
                                        </a>
                                        @can('viewKanban', $tp)
                                            <a class="btn btn-sm btn-info" href="{{ route('task-projects.kanban', $tp) }}" title="Kanban">
                                                <i data-feather="trello" style="width:14px;height:14px;"></i>
                                            </a>
                                        @endcan
                                        @can('update', $tp)
                                            <a class="btn btn-sm btn-primary" href="{{ route('task-projects.edit', $tp) }}" title="Edit">
                                                <i data-feather="edit-2" style="width:14px;height:14px;"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $tp)
                                            <form method="post" action="{{ route('task-projects.destroy', $tp) }}"
                                                  onsubmit="return confirm('Hapus project ini?')">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn-sm btn-danger" type="submit" title="Hapus">
                                                    <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada Task Project.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
