@extends('layouts.app')

@section('title', 'Detail Project')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>{{ $project->name }}</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center">
              <h5>Informasi Project</h5>
              <div>
                <a href="{{ route('projects.board', $project) }}" class="btn btn-primary btn-sm me-2">
                  <i class="fa fa-columns me-1"></i> Lihat Board
                </a>
                @can('update', $project)
                  <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning btn-sm">
                    <i class="fa fa-edit me-1"></i> Edit
                  </a>
                @endcan
              </div>
            </div>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tr>
                <th width="200">Nama Project</th>
                <td>{{ $project->name }}</td>
              </tr>
              <tr>
                <th>Deskripsi</th>
                <td>{{ $project->description ?? '-' }}</td>
              </tr>
              <tr>
                <th>Manager</th>
                <td>{{ $project->manager->name ?? '-' }}</td>
              </tr>
              <tr>
                <th>Status</th>
                <td>
                  @php
                    $statusBadges = [
                      'planning' => 'badge-light-secondary',
                      'active' => 'badge-light-success',
                      'on_hold' => 'badge-light-warning',
                      'completed' => 'badge-light-primary',
                      'cancelled' => 'badge-light-danger',
                    ];
                  @endphp
                  <span class="badge {{ $statusBadges[$project->status] ?? 'badge-light-secondary' }}">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                  </span>
                </td>
              </tr>
              <tr>
                <th>Timeline</th>
                <td>
                  @if($project->start_date && $project->end_date)
                    {{ $project->start_date->format('d M Y') }} - {{ $project->end_date->format('d M Y') }}
                  @else
                    -
                  @endif
                </td>
              </tr>
              <tr>
                <th>Budget</th>
                <td>
                  @if($project->budget)
                    Rp {{ number_format($project->budget, 0, ',', '.') }}
                  @else
                    -
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header pb-0">
            <h5>Milestones</h5>
          </div>
          <div class="card-body">
            @forelse($project->milestones as $milestone)
              <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="{{ $milestone->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                    {{ $milestone->name }}
                  </span>
                  @if($milestone->is_completed)
                    <i class="fa fa-check-circle text-success"></i>
                  @endif
                </div>
                @if($milestone->target_date)
                  <small class="text-muted">Target: {{ $milestone->target_date->format('d M Y') }}</small>
                @endif
              </div>
            @empty
              <p class="text-muted mb-0">Belum ada milestone</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
