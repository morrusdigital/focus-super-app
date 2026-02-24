@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>My Tasks</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Tasks</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <h5>Task yang Ditugaskan ke Saya <small class="text-muted">(belum selesai)</small></h5>
            <a class="btn btn-outline-danger btn-sm" href="{{ route('tasks.overdue') }}">Lihat Overdue</a>
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
                            <th>Project</th>
                            <th>Status</th>
                            <th class="text-end">Progress</th>
                            <th>Due Date</th>
                            <th style="width:100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            <tr>
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
                                <td>{{ $task->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @can('markDone', $task)
                                        <form method="post"
                                              action="{{ route('projects.tasks.status', [$task->project, $task]) }}">
                                            @csrf
                                            @method('patch')
                                            <input type="hidden" name="status" value="done">
                                            <input type="hidden" name="progress" value="100">
                                            <button class="btn btn-sm btn-success" type="submit">Done</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada task aktif yang ditugaskan ke Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
