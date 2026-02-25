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

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Project progress summary --}}
    <div class="card mb-3 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="fw-semibold small">Progress Project</span>
                <span class="fw-bold text-{{ $project->progress_percent === 100 ? 'success' : 'primary' }}">
                    {{ $project->progress_percent }}%
                </span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-{{ $project->progress_percent === 100 ? 'success' : 'primary' }}"
                     role="progressbar"
                     style="width: {{ $project->progress_percent }}%"
                     aria-valuenow="{{ $project->progress_percent }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>

    {{-- Kanban board --}}
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
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
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

                                    {{-- ─── Move actions ──────────────────────────────── --}}
                                    @can('update', $task)
                                        <form method="post"
                                              action="{{ route('tasks.move', $task) }}"
                                              class="move-form">
                                            @csrf
                                            @method('patch')

                                            <div class="d-flex gap-1 flex-wrap mb-1">
                                                @foreach (\App\Enums\TaskStatus::cases() as $s)
                                                    @if ($s->value !== $statusValue)
                                                        <button type="submit"
                                                                name="status"
                                                                value="{{ $s->value }}"
                                                                class="btn btn-xs btn-outline-{{ $columnConfig[$s->value]['color'] ?? 'secondary' }} move-btn"
                                                                data-status="{{ $s->value }}">
                                                            → {{ $s->label() }}
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>

                                            {{-- Blocked reason (shown via JS when blocked is clicked; fallback always visible) --}}
                                            <div class="blocked-reason-wrapper"
                                                 id="br-{{ $task->id }}"
                                                 style="display:none;">
                                                <textarea class="form-control form-control-sm mt-1"
                                                          name="blocked_reason"
                                                          rows="2"
                                                          placeholder="Alasan blocked (wajib)"></textarea>
                                            </div>
                                        </form>
                                    @elsecan('markDone', $task)
                                        @if ($statusValue !== 'done')
                                            <form method="post"
                                                  action="{{ route('tasks.move', $task) }}">
                                                @csrf
                                                @method('patch')
                                                <input type="hidden" name="status" value="done">
                                                <button type="submit"
                                                        class="btn btn-xs btn-outline-success w-100">
                                                    → Done
                                                </button>
                                            </form>
                                        @endif
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

    </div>{{-- /card-body (kanban grid) --}}
    </div>{{-- /card --}}
</div>
@endsection

@push('styles')
<style>
    /* Kanban grid — 4 cols desktop, 2 tablet, 1 mobile */
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
        max-height: calc(100vh - 320px);
        min-height: 120px;
    }
    .kanban-card  { transition: box-shadow .15s ease; }
    .kanban-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.15) !important; }
    .btn-xs {
        padding: .15rem .4rem;
        font-size: .72rem;
        line-height: 1.4;
        border-radius: .2rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Show blocked_reason textarea when user clicks → Blocked button.
    document.querySelectorAll('.move-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const form    = this.closest('.move-form');
            const wrapper = form.querySelector('.blocked-reason-wrapper');
            const ta      = wrapper.querySelector('textarea');
            if (this.dataset.status === 'blocked') {
                wrapper.style.display = '';
                ta.required = true;
                e.preventDefault(); // wait for user to fill reason, then re-click submit
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
