@extends('layouts.app')

@section('title', 'Task Project')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Task — {{ $project->name }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item active">Tasks</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <h5>Daftar Task</h5>
            @can('manageTasks', $project)
                <a class="btn btn-primary btn-sm" href="{{ route('projects.tasks.create', $project) }}">+ Tambah Task</a>
            @endcan
        </div>
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Status</th>
                            <th class="text-end">Progress</th>
                            <th>Due Date</th>
                            <th>Assignees</th>
                            <th style="width:140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
                                <td>{{ $task->title }}</td>
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
                                <td>{{ $task->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @forelse ($task->assignees as $assignee)
                                        <span class="badge badge-light-info">{{ $assignee->name }}</span>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td class="d-flex gap-1">
                                    @can('update', $task)
                                        <a class="btn btn-sm btn-warning"
                                           href="{{ route('projects.tasks.edit', [$project, $task]) }}">Edit</a>
                                    @endcan
                                    @can('markDone', $task)
                                        @if ($task->status->value !== 'done')
                                            <form method="post"
                                                  action="{{ route('projects.tasks.status', [$project, $task]) }}">
                                                @csrf
                                                @method('patch')
                                                <input type="hidden" name="status" value="done">
                                                <input type="hidden" name="progress" value="100">
                                                <button class="btn btn-sm btn-success" type="submit">Done</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada task.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
