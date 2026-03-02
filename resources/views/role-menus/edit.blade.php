@extends('layouts.app')

@section('title', 'Atur Menu — ' . ($roleLabels[$role] ?? $role))

@push('styles')
<style>
  /* ── Menu item card — unchecked state ─────────────────────────── */
  .menu-item-card {
    background-color: #ffffff;
    border-color: #dee2e6 !important;
    color: #212529;
    cursor: pointer;
    transition: all .15s ease;
  }
  .menu-item-card .menu-label {
    color: #212529;
    font-weight: 600;
  }
  .menu-item-card .menu-desc {
    color: #6c757d;
    font-size: .75rem;
  }
  .menu-item-card .menu-icon {
    color: var(--theme-default, #3461ff);
    width: 15px;
    height: 15px;
    flex-shrink: 0;
  }

  /* ── Menu item card — checked / active state ──────────────────── */
  .menu-item-card.is-checked {
    background-color: var(--theme-default, #3461ff) !important;
    border-color: var(--theme-default, #3461ff) !important;
    color: #ffffff;
  }
  .menu-item-card.is-checked .menu-label {
    color: #ffffff;
  }
  .menu-item-card.is-checked .menu-desc {
    color: rgba(255, 255, 255, 0.80);
  }
  .menu-item-card.is-checked .menu-icon {
    color: #ffffff;
  }

  /* Checkbox itself */
  .menu-item-card .form-check-input {
    flex-shrink: 0;
    margin-top: 2px;
  }
</style>
@endpush

@section('content')
  <div class="container-fluid">
    {{-- Page title --}}
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Atur Menu: {{ $roleLabels[$role] ?? $role }}</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('role-menus.index') }}">Manajemen Role Menu</a></li>
            <li class="breadcrumb-item active">{{ $roleLabels[$role] ?? $role }}</li>
          </ol>
        </div>
      </div>
    </div>

    <form method="POST" action="{{ route('role-menus.update', $role) }}" id="roleMenuForm">
      @csrf
      @method('PUT')

      <div class="row g-3">
        {{-- Left column: info + action --}}
        <div class="col-lg-4 col-xl-3">
          <div class="card sticky-top" style="top:20px;">
            <div class="card-header pb-0">
              <h5 class="mb-0">
                <i data-feather="info" style="width:16px;height:16px;margin-right:4px;"></i>
                Info Role
              </h5>
            </div>
            <div class="card-body">
              <table class="table table-sm table-borderless mb-0">
                <tr>
                  <th class="text-muted fw-normal small ps-0">Role</th>
                  <td><span class="badge bg-primary">{{ $role }}</span></td>
                </tr>
                <tr>
                  <th class="text-muted fw-normal small ps-0">Nama</th>
                  <td class="fw-semibold">{{ $roleLabels[$role] ?? $role }}</td>
                </tr>
                <tr>
                  <th class="text-muted fw-normal small ps-0">Dipilih</th>
                  <td>
                    <span id="selectedCount" class="fw-semibold text-primary">{{ count($currentKeys) }}</span>
                    / {{ count($catalog) }} menu
                  </td>
                </tr>
              </table>
            </div>
            <div class="card-footer d-grid gap-2 bg-transparent">
              <button type="submit" class="btn btn-primary">
                <i data-feather="save" style="width:14px;height:14px;margin-right:4px;"></i>
                Simpan Perubahan
              </button>
              <a href="{{ route('role-menus.index') }}" class="btn btn-light">
                Batal
              </a>
            </div>
          </div>
        </div>

        {{-- Right column: menu item checklist --}}
        <div class="col-lg-8 col-xl-9">
          <div class="card">
            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
              <h5 class="mb-0">Pilih Menu yang Dapat Diakses</h5>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAll">
                  Pilih Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                  Hapus Semua
                </button>
              </div>
            </div>
            <div class="card-body">

              @php
                // Separate master group from regular items
                $masterItems  = array_filter($catalog, fn($d) => ($d['group'] ?? null) === 'master');
                $regularItems = array_filter($catalog, fn($d) => ($d['group'] ?? null) !== 'master');
              @endphp

              {{-- Regular menu items --}}
              <div class="row g-2 mb-4">
                @foreach ($regularItems as $key => $item)
                  <div class="col-md-6">
                    <label class="menu-item-card d-flex align-items-start gap-3 p-3 rounded border {{ in_array($key, $currentKeys) ? 'is-checked' : '' }}"
                           for="menu_{{ $key }}">
                      <input type="checkbox"
                             name="menu_keys[]"
                             id="menu_{{ $key }}"
                             value="{{ $key }}"
                             class="form-check-input menu-checkbox"
                             {{ in_array($key, $currentKeys) ? 'checked' : '' }}>
                      <div class="grow">
                        <div class="d-flex align-items-center gap-2 mb-1">
                          <i data-feather="{{ $item['icon'] }}" class="menu-icon"></i>
                          <span class="menu-label small">{{ $item['label'] }}</span>
                        </div>
                        <p class="menu-desc mb-0">{{ $item['description'] ?? '' }}</p>
                      </div>
                    </label>
                  </div>
                @endforeach
              </div>

              {{-- Master group section --}}
              @if (!empty($masterItems))
                <div class="mb-2">
                  <h6 class="text-muted d-flex align-items-center gap-2">
                    <i data-feather="folder" style="width:15px;height:15px;"></i>
                    Menu Master Data
                    <small class="fw-normal">(akan ditampilkan dalam submenu lipat "Master")</small>
                  </h6>
                </div>
                <div class="row g-2">
                  @foreach ($masterItems as $key => $item)
                    <div class="col-md-6">
                      <label class="menu-item-card d-flex align-items-start gap-3 p-3 rounded border {{ in_array($key, $currentKeys) ? 'is-checked' : '' }}"
                             for="menu_{{ $key }}">
                        <input type="checkbox"
                               name="menu_keys[]"
                               id="menu_{{ $key }}"
                               value="{{ $key }}"
                               class="form-check-input menu-checkbox"
                               {{ in_array($key, $currentKeys) ? 'checked' : '' }}>
                        <div class="grow">
                          <div class="d-flex align-items-center gap-2 mb-1">
                            <i data-feather="{{ $item['icon'] }}" class="menu-icon"></i>
                            <span class="menu-label small">{{ $item['label'] }}</span>
                          </div>
                          <p class="menu-desc mb-0">{{ $item['description'] ?? '' }}</p>
                        </div>
                      </label>
                    </div>
                  @endforeach
                </div>
              @endif

            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script>
  (function () {
    const checkboxes = document.querySelectorAll('.menu-checkbox');
    const countEl    = document.getElementById('selectedCount');

    // Update selected count
    function updateCount() {
      const checked = document.querySelectorAll('.menu-checkbox:checked').length;
      countEl.textContent = checked;
    }

    // Toggle card highlight when checkbox changes
    checkboxes.forEach(function (cb) {
      cb.addEventListener('change', function () {
        const card = cb.closest('.menu-item-card');
        card.classList.toggle('is-checked', cb.checked);
        updateCount();
      });
    });

    // Select all
    document.getElementById('btnSelectAll').addEventListener('click', function () {
      checkboxes.forEach(function (cb) {
        cb.checked = true;
        cb.closest('.menu-item-card').classList.add('is-checked');
      });
      updateCount();
    });

    // Deselect all
    document.getElementById('btnDeselectAll').addEventListener('click', function () {
      checkboxes.forEach(function (cb) {
        cb.checked = false;
        cb.closest('.menu-item-card').classList.remove('is-checked');
      });
      updateCount();
    });
  })();
</script>
@endpush
