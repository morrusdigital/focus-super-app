@extends('layouts.app')

@section('title', 'Kanban — ' . $project->name)

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Kanban — {{ $project->name }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item active">Kanban</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Kanban board --}}
    <div class="row flex-nowrap overflow-auto pb-3 kanban-board">

        @php
            $columnConfig = [
                'todo'    => ['color' => 'secondary', 'header_bg' => '#6c757d'],
                'doing'   => ['color' => 'primary',   'header_bg' => '#007bff'],
                'blocked' => ['color' => 'danger',    'header_bg' => '#dc3545'],
                'done'    => ['color' => 'success',   'header_bg' => '#28a745'],
            ];
        @endphp

        @foreach ($columns as $statusValue => $column)
            @php $cfg = $columnConfig[$statusValue] ?? ['color' => 'secondary', 'header_bg' => '#6c757d']; @endphp
            <div class="col-12 col-md-6 col-xl-3" style="min-width: 280px;">
                <div class="card h-100 shadow-sm">
                    {{-- Column header --}}
                    <div class="card-header text-white py-2"
                         style="background-color: {{ $cfg['header_bg'] }};">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold">{{ $column['label'] }}</span>
                            <span class="badge bg-white text-dark">{{ $column['tasks']->count() }}</span>
                        </div>
                    </div>

                    {{-- Task cards --}}
                    <div class="card-body p-2 d-flex flex-column gap-2"
                         style="overflow-y: auto; max-height: 70vh;">

                        @forelse ($column['tasks'] as $task)
                            <div class="card border shadow-none kanban-card">
                                <div class="card-body p-2">

                                    {{-- Title --}}
                                    <p class="fw-semibold mb-1 text-truncate" title="{{ $task->title }}">
                                        {{ $task->title }}
                                    </p>

                                    {{-- Blocked reason badge --}}
                                    @if ($task->blocked_reason)
                                        <p class="text-danger small mb-1">
                                            <i class="fa fa-ban me-1"></i>{{ Str::limit($task->blocked_reason, 60) }}
                                        </p>
                                    @endif

                                    {{-- Progress bar --}}
                                    <div class="progress mb-2" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $cfg['color'] }}"
                                             role="progressbar"
                                             style="width: {{ $task->progress }}%"
                                             aria-valuenow="{{ $task->progress }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>

                                    {{-- Footer: due date + assignees --}}
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                        {{-- Due date --}}
                                        <small class="{{ $task->due_date && $task->due_date->isPast() && $statusValue !== 'done' ? 'text-danger fw-bold' : 'text-muted' }}">
                                            <i class="fa fa-calendar me-1"></i>
                                            {{ $task->due_date?->format('d/m/Y') ?? '-' }}
                                        </small>

                                        {{-- Assignees --}}
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($task->assignees as $assignee)
                                                <span class="badge badge-light-info"
                                                      title="{{ $assignee->name }}">
                                                    {{ Str::limit($assignee->name, 12) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>{{-- /card-body --}}
                            </div>{{-- /kanban-card --}}
                        @empty
                            <div class="text-center text-muted py-4" style="font-size:.85rem;">
                                Tidak ada task.
                            </div>
                        @endforelse

                    </div>{{-- /card-body (column) --}}
                </div>{{-- /card (column) --}}
            </div>{{-- /col --}}
        @endforeach

    </div>{{-- /kanban-board --}}
</div>
@endsection

@push('styles')
<style>
    .kanban-board {
        align-items: flex-start;
    }
    .kanban-card {
        transition: box-shadow .15s ease;
    }
    .kanban-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,.15) !important;
    }
</style>
@endpush
