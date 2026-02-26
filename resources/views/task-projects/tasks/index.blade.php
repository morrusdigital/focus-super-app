@extends('layouts.app')

@section('title', 'Tasks — ' . $taskProject->name)

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Tasks — {{ $taskProject->name }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.index') }}">Task Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.show', $taskProject) }}">{{ $taskProject->name }}</a></li>
                    <li class="breadcrumb-item active">Tasks</li>
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

    {{-- Summary bar --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="card border shadow-none text-center p-2">
                <div class="text-muted small">Progress</div>
                <div class="fw-bold text-{{ $summary['progress_percent'] === 100 ? 'success' : 'primary' }}">
                    {{ $summary['progress_percent'] }}%
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border shadow-none text-center p-2">
                <div class="text-muted small">Total</div>
                <div class="fw-bold">{{ $summary['total_tasks'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border shadow-none text-center p-2">
                <div class="text-muted small">To Do</div>
                <div class="fw-bold text-secondary">{{ $summary['todo'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border shadow-none text-center p-2">
                <div class="text-muted small">In Progress</div>
                <div class="fw-bold text-primary">{{ $summary['doing'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border shadow-none text-center p-2">
                <div class="text-muted small">Blocked</div>
                <div class="fw-bold text-danger">{{ $summary['blocked'] }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border shadow-none text-center p-2">
                <div class="text-muted small">Done</div>
                <div class="fw-bold text-success">{{ $summary['done'] }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <h5>Daftar Task</h5>
            <div class="d-flex gap-2">
                <a class="btn btn-info btn-sm" href="{{ route('task-projects.kanban', $taskProject) }}">
                    <i data-feather="trello" style="width:14px;height:14px;"></i> Kanban
                </a>
                @can('manageTasks', $taskProject)
                    <a class="btn btn-primary btn-sm" href="{{ route('task-projects.tasks.create', $taskProject) }}">
                        <i data-feather="plus-circle" style="width:14px;height:14px;"></i> Tambah Task
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordernone">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Due Date</th>
                            <th>Assignees</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Enums\TaskStatus::cases() as $statusEnum)
                            @php
                                $statusTasks = $tasks->filter(fn($t) => $t->status->value === $statusEnum->value);
                                $colorMap = ['todo'=>'secondary','doing'=>'primary','blocked'=>'danger','done'=>'success'];
                                $color = $colorMap[$statusEnum->value] ?? 'secondary';
                            @endphp
                            @if ($statusTasks->isNotEmpty())
                                <tr class="table-{{ $color === 'secondary' ? 'light' : $color }}">
                                    <td colspan="6" class="fw-semibold py-1 small">
                                        {{ $statusEnum->label() }} ({{ $statusTasks->count() }})
                                    </td>
                                </tr>
                                @foreach ($statusTasks as $task)
                                    <tr>
                                        <td>
                                            {{ $task->title }}
                                            @if ($task->blocked_reason)
                                                <br><small class="text-danger"><i data-feather="alert-circle" style="width:12px;height:12px;"></i> {{ Str::limit($task->blocked_reason, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light-{{ $color }}">{{ $task->status->label() }}</span>
                                        </td>
                                        <td style="min-width:90px;">
                                            <div class="d-flex align-items-center gap-1">
                                                <div class="progress grow" style="height:6px;">
                                                    <div class="progress-bar bg-{{ $color }}"
                                                         style="width:{{ $task->progress }}%"></div>
                                                </div>
                                                <small>{{ $task->progress }}%</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($task->due_date)
                                                <span class="{{ $task->due_date->isPast() && $task->status->value !== 'done' ? 'text-danger fw-bold' : '' }}">
                                                    {{ $task->due_date->format('d/m/Y') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($task->assignees as $a)
                                                    <span class="badge badge-light-info" title="{{ $a->name }}">
                                                        {{ Str::limit($a->name, 12) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                @can('update', $task)
                                                    <a class="btn btn-xs btn-primary"
                                                       href="{{ route('task-projects.tasks.edit', [$taskProject, $task]) }}">
                                                        <i data-feather="edit-2" style="width:12px;height:12px;"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $task)
                                                    <form method="post"
                                                          action="{{ route('task-projects.tasks.destroy', [$taskProject, $task]) }}"
                                                          onsubmit="return confirm('Hapus task ini?')">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-xs btn-danger" type="submit">
                                                            <i data-feather="trash-2" style="width:12px;height:12px;"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                        @if ($tasks->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada task.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-xs { padding:.15rem .4rem; font-size:.72rem; line-height:1.4; border-radius:.2rem; }
</style>
@endpush
