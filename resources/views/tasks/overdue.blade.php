@extends('layouts.app')

@section('title', 'Overdue Tasks')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Overdue Tasks</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Overdue</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <h5>Task Terlambat <small class="text-muted">(due date sudah lewat, belum selesai)</small></h5>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('tasks.my') }}">My Tasks</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th class="text-end">Progress</th>
                            <th>Due Date</th>
                            <th>Assignees</th>
                            <th style="width:100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr class="table-danger">
                                <td>{{ $task->title }}</td>
                                <td>
                                    @if ($task->project)
                                        <a href="{{ route('projects.tasks.index', $task->project) }}">
                                            {{ $task->project->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badge = match($task->status->value) {
                                            'todo'    => 'badge-light-secondary',
                                            'doing'   => 'badge-light-primary',
                                            'blocked' => 'badge-light-danger',
                                            'done'    => 'badge-light-success',
                                            default   => 'badge-light-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $task->status->label() }}</span>
                                </td>
                                <td class="text-end">{{ $task->progress }}%</td>
                                <td class="text-danger fw-bold">
                                    {{ $task->due_date?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td>
                                    @forelse ($task->assignees as $assignee)
                                        <span class="badge badge-light-info">{{ $assignee->name }}</span>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td>
                                    @can('update', $task)
                                        <a class="btn btn-sm btn-warning"
                                           href="{{ route('projects.tasks.edit', [$task->project, $task]) }}">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Tidak ada task overdue. 🎉</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
