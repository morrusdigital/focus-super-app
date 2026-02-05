@extends('layouts.app')

@section('title', 'Portfolio Dashboard')

@section('content')
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Portfolio Dashboard</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Portfolio</li>
          </ol>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
      <div class="col-xl-3 col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Total Projects</h6>
                <h3 class="mb-0">{{ $totalProjects }}</h3>
              </div>
              <div class="badge-light-primary p-3 rounded">
                <i data-feather="briefcase" class="text-primary"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Total Tasks</h6>
                <h3 class="mb-0">{{ $totalCards }}</h3>
              </div>
              <div class="badge-light-info p-3 rounded">
                <i data-feather="check-square" class="text-info"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Avg Progress</h6>
                <h3 class="mb-0">{{ $avgProgress }}%</h3>
              </div>
              <div class="badge-light-success p-3 rounded">
                <i data-feather="trending-up" class="text-success"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Overdue Tasks</h6>
                <h3 class="mb-0 {{ $overdueTasks > 0 ? 'text-danger' : '' }}">{{ $overdueTasks }}</h3>
              </div>
              <div class="badge-light-danger p-3 rounded">
                <i data-feather="alert-circle" class="text-danger"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
      <div class="col-xl-6">
        <div class="card">
          <div class="card-header pb-0">
            <h5>Projects by Status</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-borderless">
                <tbody>
                  @foreach(['planning' => 'Planning', 'active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $status => $label)
                    @php
                      $count = $projectsByStatus[$status] ?? 0;
                      $percentage = $totalProjects > 0 ? round(($count / $totalProjects) * 100) : 0;
                      $badgeColors = [
                        'planning' => 'secondary',
                        'active' => 'success',
                        'on_hold' => 'warning',
                        'completed' => 'primary',
                        'cancelled' => 'danger'
                      ];
                    @endphp
                    <tr>
                      <td width="150">
                        <span class="badge badge-light-{{ $badgeColors[$status] }}">{{ $label }}</span>
                      </td>
                      <td>
                        <div class="progress mt-1" style="height: 20px;">
                          <div class="progress-bar bg-{{ $badgeColors[$status] }}" role="progressbar"
                               style="width: {{ $percentage }}%">
                            {{ $count }}
                          </div>
                        </div>
                      </td>
                      <td width="60" class="text-end">{{ $percentage }}%</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card">
          <div class="card-header pb-0">
            <h5>Tasks by Status</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-borderless">
                <tbody>
                  @php
                    $columnColors = [
                      'Backlog' => 'secondary',
                      'To Do' => 'primary',
                      'In Progress' => 'warning',
                      'Review' => 'info',
                      'Testing' => 'purple',
                      'Done' => 'success',
                      'Cancelled' => 'danger'
                    ];
                  @endphp
                  @foreach(['Backlog', 'To Do', 'In Progress', 'Review', 'Testing', 'Done', 'Cancelled'] as $column)
                    @php
                      $count = $cardsByColumn[$column] ?? 0;
                      $percentage = $totalCards > 0 ? round(($count / $totalCards) * 100) : 0;
                    @endphp
                    <tr>
                      <td width="150">
                        <span class="badge badge-light-{{ $columnColors[$column] ?? 'secondary' }}">{{ $column }}</span>
                      </td>
                      <td>
                        <div class="progress mt-1" style="height: 20px;">
                          <div class="progress-bar bg-{{ $columnColors[$column] ?? 'secondary' }}" role="progressbar"
                               style="width: {{ $percentage }}%">
                            {{ $count }}
                          </div>
                        </div>
                      </td>
                      <td width="60" class="text-end">{{ $percentage }}%</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Projects -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0">
            <h5>Top Projects by Progress</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Project Name</th>
                    <th>Status</th>
                    <th>Tasks</th>
                    <th>Progress</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($topProjects as $project)
                    <tr>
                      <td>
                        <strong>{{ $project['name'] }}</strong>
                      </td>
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
                        <span class="badge {{ $statusBadges[$project['status']] ?? 'badge-light-secondary' }}">
                          {{ ucfirst(str_replace('_', ' ', $project['status'])) }}
                        </span>
                      </td>
                      <td>{{ $project['done_cards'] }}/{{ $project['total_cards'] }}</td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="progress flex-grow-1 me-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar"
                                 style="width: {{ $project['progress'] }}%"></div>
                          </div>
                          <small>{{ $project['progress'] }}%</small>
                        </div>
                      </td>
                      <td>
                        <a href="{{ route('projects.board', $project['id']) }}" class="btn btn-sm btn-primary">
                          <i class="fa fa-columns"></i> Board
                        </a>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted">Belum ada project</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
