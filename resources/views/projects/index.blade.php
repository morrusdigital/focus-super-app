@extends('layouts.app')

@section('title', 'Projects')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Projects</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Projects</li>
          </ol>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between">
              <h5>Daftar Project</h5>
              @can('create', App\Models\Project::class)
                <a class="btn btn-primary" href="{{ route('projects.create') }}">
                  <i class="fa fa-plus me-1"></i>Tambah Project
                </a>
              @endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Nama Project</th>
                    <th>Manager</th>
                    <th>Status</th>
                    <th>Timeline</th>
                    <th>Budget</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($projects as $project)
                    <tr>
                      <td>
                        <strong>{{ $project->name }}</strong>
                        @if($project->description)
                          <br><small class="text-muted">{{ Str::limit($project->description, 50) }}</small>
                        @endif
                      </td>
                      <td>{{ $project->manager->name ?? '-' }}</td>
                      <td>
                        @php
                          $statusBadges = [
                            'planning' => 'badge-light-secondary',
                            'active' => 'badge-light-success',
                            'on_hold' => 'badge-light-warning',
                            'completed' => 'badge-light-primary',
                            'cancelled' => 'badge-light-danger',
                          ];
                          $statusLabels = [
                            'planning' => 'Planning',
                            'active' => 'Active',
                            'on_hold' => 'On Hold',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                          ];
                        @endphp
                        <span class="badge {{ $statusBadges[$project->status] ?? 'badge-light-secondary' }}">
                          {{ $statusLabels[$project->status] ?? $project->status }}
                        </span>
                      </td>
                      <td>
                        @if($project->start_date && $project->end_date)
                          <small>{{ $project->start_date->format('d M Y') }} - {{ $project->end_date->format('d M Y') }}</small>
                        @else
                          -
                        @endif
                      </td>
                      <td>
                        @if($project->budget)
                          Rp {{ number_format($project->budget, 0, ',', '.') }}
                        @else
                          -
                        @endif
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          <a href="{{ route('projects.board', $project) }}" class="btn btn-sm btn-primary" title="Kanban Board">
                            <i class="fa fa-columns"></i>
                          </a>
                          <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info" title="Detail">
                            <i class="fa fa-eye"></i>
                          </a>
                          @can('update', $project)
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-warning" title="Edit">
                              <i class="fa fa-edit"></i>
                            </a>
                          @endcan
                          @can('delete', $project)
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus project ini?')">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                <i class="fa fa-trash"></i>
                              </button>
                            </form>
                          @endcan
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center py-4">
                        <p class="mb-0 text-muted">Belum ada project</p>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($projects->hasPages())
              <div class="mt-3">
                {{ $projects->links() }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
