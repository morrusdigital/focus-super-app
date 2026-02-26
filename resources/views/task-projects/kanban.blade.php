@extends('layouts.app')

@section('title', 'Kanban — ' . $taskProject->name)

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Kanban — {{ $taskProject->name }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.index') }}">Task Projects</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('task-projects.show', $taskProject) }}">{{ $taskProject->name }}</a></li>
                    <li class="breadcrumb-item active">Kanban</li>
                </ol>
            </div>
        </div>
    </div>

    @if (session('status') || session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') ?? session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Summary bar ─────────────────────────────────────────── --}}
    <div class="card mb-3 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center g-2">
                <div class="col-auto">
                    @php
                        $statusBadge = ['not_started'=>'secondary','on_track'=>'primary','blocked'=>'danger','done'=>'success'];
                        $statusLabel = ['not_started'=>'Belum Mulai','on_track'=>'On Track','blocked'=>'Blocked','done'=>'Selesai'];
                        $st = $summary['project_status'];
                    @endphp
                    <span class="badge badge-light-{{ $statusBadge[$st] ?? 'secondary' }}">
                        {{ $statusLabel[$st] ?? $st }}
                    </span>
                </div>
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="fw-semibold">Progress Project</small>
                        <small class="fw-bold text-{{ $summary['progress_percent'] === 100 ? 'success' : 'primary' }}">
                            {{ $summary['progress_percent'] }}%
                        </small>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-{{ $summary['progress_percent'] === 100 ? 'success' : 'primary' }}"
                             role="progressbar"
                             style="width:{{ $summary['progress_percent'] }}%"
                             aria-valuenow="{{ $summary['progress_percent'] }}"
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <div class="col-auto text-muted small">
                    Todo: {{ $summary['todo'] }} &bull;
                    Doing: {{ $summary['doing'] }} &bull;
                    Blocked: {{ $summary['blocked'] }} &bull;
                    Done: {{ $summary['done'] }}
                </div>
                @can('manageTasks', $taskProject)
                    <div class="col-auto">
                        <a class="btn btn-primary btn-sm"
                           href="{{ route('task-projects.tasks.create', $taskProject) }}">
                            <i data-feather="plus-circle" style="width:14px;height:14px;"></i> Task Baru
                        </a>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- ── Kanban board ────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-body kanban-grid">

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
                <div class="kanban-col">
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
                        <div class="card-body p-2 d-flex flex-column gap-2 kanban-col-body">

                            @forelse ($column['tasks'] as $task)
                                <div class="card border shadow-none kanban-card">
                                    <div class="card-body p-2">

                                        {{-- Title --}}
                                        <p class="fw-semibold mb-1 text-truncate" title="{{ $task->title }}">
                                            {{ $task->title }}
                                        </p>

                                        {{-- Blocked reason --}}
                                        @if ($task->blocked_reason)
                                            <p class="text-danger small mb-1">
                                                <i class="fa fa-ban me-1"></i>{{ Str::limit($task->blocked_reason, 60) }}
                                            </p>
                                        @endif

                                        {{-- Progress bar --}}
                                        <div class="progress mb-2" style="height:5px;">
                                            <div class="progress-bar bg-{{ $cfg['color'] }}"
                                                 role="progressbar"
                                                 style="width:{{ $task->progress }}%"
                                                 aria-valuenow="{{ $task->progress }}"
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>

                                        {{-- Footer: due date + assignees --}}
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
                                            <small class="{{ $task->due_date && $task->due_date->isPast() && $statusValue !== 'done' ? 'text-danger fw-bold' : 'text-muted' }}">
                                                <i class="fa fa-calendar me-1"></i>
                                                {{ $task->due_date?->format('d/m/Y') ?? '-' }}
                                            </small>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($task->assignees as $assignee)
                                                    <span class="badge badge-light-info" title="{{ $assignee->name }}">
                                                        {{ Str::limit($assignee->name, 12) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- ── Move actions ──────────────────────────── --}}
                                        @can('moveStatus', $task)
                                            <form method="post"
                                                  action="{{ route('task-projects.tasks.move', [$taskProject, $task]) }}"
                                                  class="tp-move-form">
                                                @csrf
                                                @method('patch')

                                                <div class="d-flex gap-1 flex-wrap mb-1">
                                                    @foreach (\App\Enums\TaskStatus::cases() as $s)
                                                        @if ($s->value !== $statusValue)
                                                            <button type="submit"
                                                                    name="status"
                                                                    value="{{ $s->value }}"
                                                                    class="btn btn-xs btn-outline-{{ $columnConfig[$s->value]['color'] ?? 'secondary' }} tp-move-btn"
                                                                    data-status="{{ $s->value }}">
                                                                → {{ $s->label() }}
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                {{-- Blocked reason textarea (shown via JS) --}}
                                                <div class="tp-blocked-reason-wrapper"
                                                     id="tp-br-{{ $task->id }}"
                                                     style="display:none;">
                                                    <textarea class="form-control form-control-sm mt-1"
                                                              name="blocked_reason"
                                                              rows="2"
                                                              placeholder="Alasan blocked (wajib)"></textarea>
                                                </div>
                                            </form>
                                        @endcan

                                    </div>{{-- /card-body --}}
                                </div>{{-- /kanban-card --}}
                            @empty
                                <div class="text-center text-muted py-4" style="font-size:.85rem;">
                                    Tidak ada task.
                                </div>
                            @endforelse

                        </div>{{-- /card-body (column) --}}
                    </div>{{-- /card (column) --}}
                </div>{{-- /kanban-col --}}
            @endforeach

        </div>{{-- /card-body (grid) --}}
    </div>{{-- /card --}}
</div>
@endsection

@push('styles')
<style>
    .kanban-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    }
    .kanban-col { min-width: 0; }
    @media (max-width: 991px) {
        .kanban-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575px) {
        .kanban-grid { grid-template-columns: minmax(0, 1fr); }
    }
    .kanban-col-body {
        overflow-y: auto;
        max-height: calc(100vh - 340px);
        min-height: 120px;
    }
    .kanban-card { transition: box-shadow .15s ease; }
    .kanban-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.15) !important; }
    .btn-xs { padding:.15rem .4rem; font-size:.72rem; line-height:1.4; border-radius:.2rem; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tp-move-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const form    = this.closest('.tp-move-form');
            const wrapper = form.querySelector('.tp-blocked-reason-wrapper');
            const ta      = wrapper.querySelector('textarea');
            if (this.dataset.status === 'blocked') {
                wrapper.style.display = '';
                ta.required = true;
                e.preventDefault();
            } else {
                wrapper.style.display = 'none';
                ta.required = false;
                ta.value    = '';
            }
        });
    });
});
</script>
@endpush
