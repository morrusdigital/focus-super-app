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

    <div class="card">
      <div class="card-header pb-0 d-flex align-items-center justify-content-between">
        <h5>Daftar Project</h5>
        @can('create', \App\Models\Project::class)
          <a class="btn btn-primary" href="{{ route('projects.create') }}">Tambah Project</a>
        @endcan
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordernone">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Nilai Kontrak</th>
                <th>PPH</th>
                <th>PPN</th>
                <th>Status Data</th>
                <th>Dibuat</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($projects as $project)
                <tr>
                  <td>{{ $project->name }}</td>
                  <td>
                    @if ($project->contract_value !== null)
                      Rp {{ number_format($project->contract_value, 2, ',', '.') }}
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    @if ($project->use_pph === null)
                      -
                    @elseif (!$project->use_pph)
                      Tidak
                    @else
                      Ya ({{ $project->pph_rate !== null ? number_format($project->pph_rate, 2, ',', '.') . '%' : '-' }})
                    @endif
                  </td>
                  <td>
                    @if ($project->use_ppn === null)
                      -
                    @elseif (!$project->use_ppn)
                      Tidak
                    @else
                      Ya ({{ $project->ppn_rate !== null ? number_format($project->ppn_rate, 2, ',', '.') . '%' : '-' }})
                    @endif
                  </td>
                  <td>
                    @if ($project->isTaxConfigurationComplete())
                      <span class="badge badge-light-success">Lengkap</span>
                    @else
                      <span class="badge badge-light-warning">Belum Lengkap</span>
                    @endif
                  </td>
                  <td>{{ $project->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                  <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end flex-nowrap">
                      <a class="btn btn-sm btn-light" href="{{ route('projects.show', $project) }}" title="Detail">
                        <i data-feather="eye" style="width:14px;height:14px;"></i>
                      </a>
                      @can('viewKanban', $project)
                        <a class="btn btn-sm btn-info" href="{{ route('projects.kanban', $project) }}" title="Kanban">
                          <i data-feather="trello" style="width:14px;height:14px;"></i>
                        </a>
                      @endcan
                      @can('update', $project)
                        <a class="btn btn-sm btn-primary" href="{{ route('projects.edit', $project) }}" title="Edit">
                          <i data-feather="edit-2" style="width:14px;height:14px;"></i>
                        </a>
                      @endcan
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center">Belum ada project.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
