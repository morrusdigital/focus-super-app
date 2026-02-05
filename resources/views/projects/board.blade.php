@extends('layouts.app')

@section('title', 'Kanban Board - ' . $project->name)

@push('styles')
<style>
/* Force override all conflicting styles */
.kanban-wrapper {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
}

.kanban-board {
    display: -webkit-box !important;
    display: -ms-flexbox !important;
    display: flex !important;
    -webkit-box-orient: horizontal !important;
    -webkit-box-direction: normal !important;
    -ms-flex-direction: row !important;
    flex-direction: row !important;
    -ms-flex-wrap: nowrap !important;
    flex-wrap: nowrap !important;
    gap: 16px !important;
    padding: 20px 0 !important;
    min-height: 500px !important;
    width: max-content !important;
    min-width: 100% !important;
}

/* Kanban Column - Fixed Width */
.kanban-column {
    -webkit-box-flex: 0 !important;
    -ms-flex: 0 0 320px !important;
    flex: 0 0 320px !important;
    min-width: 320px !important;
    max-width: 320px !important;
    width: 320px !important;
    background: #f8f9fa !important;
    border-radius: 12px !important;
    display: -webkit-box !important;
    display: -ms-flexbox !important;
    display: flex !important;
    -webkit-box-orient: vertical !important;
    -webkit-box-direction: normal !important;
    -ms-flex-direction: column !important;
    flex-direction: column !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

/* Custom Scrollbar for Kanban Board */
.kanban-board::-webkit-scrollbar {
    height: 12px !important;
}

.kanban-board::-webkit-scrollbar-track {
    background: #e9ecef !important;
    border-radius: 10px !important;
    margin: 0 10px !important;
}

.kanban-board::-webkit-scrollbar-thumb {
    background: #6c757d !important;
    border-radius: 10px !important;
    border: 2px solid #e9ecef !important;
}

.kanban-board::-webkit-scrollbar-thumb:hover {
    background: #495057 !important;
}

.card-body::-webkit-scrollbar {
    height: 12px !important;
}

.card-body::-webkit-scrollbar-track {
    background: #f8f9fa !important;
    border-radius: 10px !important;
}

.card-body::-webkit-scrollbar-thumb {
    background: #6c757d !important;
    border-radius: 10px !important;
}

.card-body::-webkit-scrollbar-thumb:hover {
    background: #495057 !important;
}

/* Column Header */
.kanban-column-header {
    padding: 16px;
    border-top: 4px solid;
    border-radius: 12px 12px 0 0;
    background: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
}

.kanban-column-title {
    font-size: 12px;
    font-weight: 700;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #495057;
}

.kanban-column-count {
    background: rgba(0,0,0,0.1);
    color: #495057;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    min-width: 28px;
    text-align: center;
}

/* Cards Container */
.kanban-cards {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: calc(100vh - 450px);
}

.kanban-cards::-webkit-scrollbar {
    width: 6px;
}

.kanban-cards::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-cards::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 10px;
}

.kanban-cards::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Kanban Card */
.kanban-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
    cursor: move;
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
    position: relative;
}

.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.kanban-card.dragging {
    opacity: 0.6;
    transform: rotate(3deg);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.kanban-card-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #212529;
    line-height: 1.5;
}

.kanban-card-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 12px;
    align-items: center;
}

.kanban-card-assignee {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #6c757d;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
}

.kanban-card-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--theme-deafult);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 600;
}

.kanban-card-due {
    font-size: 11px;
    color: #6c757d;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
}

.kanban-card-due.overdue {
    color: #dc3545;
    background: #fee;
    font-weight: 600;
}

.kanban-card-progress {
    margin-top: 12px;
}

.progress-text {
    font-size: 10px;
    color: #6c757d;
    margin-bottom: 6px;
    display: flex;
    justify-content: space-between;
}

.add-card-btn {
    width: 100%;
    padding: 12px;
    background: white;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
    font-weight: 600;
}

.add-card-btn:hover {
    background: #f8f9fa;
    border-color: var(--theme-deafult);
    color: var(--theme-deafult);
}

.add-card-btn i {
    margin-right: 6px;
}

.project-info-card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.milestone-progress {
    background: white;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.milestone-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f3f5;
    transition: background 0.2s;
}

.milestone-item:hover {
    background: #f8f9fa;
}

.milestone-item:last-child {
    border-bottom: none;
}

.milestone-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.milestone-name {
    flex: 1;
    font-size: 13px;
    line-height: 1.4;
}

.milestone-date {
    font-size: 11px;
    color: #6c757d;
    white-space: nowrap;
}
</style>
@endpush

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
            <li class="breadcrumb-item active">Kanban Board</li>
          </ol>
        </div>
      </div>
    </div>

    <!-- Project Info & Milestones -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card project-info-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <h5 class="mb-0">{{ $project->name }}</h5>
                  @php
                    $statusBadges = [
                      'planning' => 'secondary',
                      'active' => 'success',
                      'on_hold' => 'warning',
                      'completed' => 'primary',
                      'cancelled' => 'danger',
                    ];
                  @endphp
                  <span class="badge badge-{{ $statusBadges[$project->status] ?? 'secondary' }}">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                  </span>
                </div>
                @if($project->description)
                  <p class="text-muted mb-0" style="font-size: 14px;">{{ $project->description }}</p>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header pb-0">
            <h6 class="mb-0">Milestones</h6>
          </div>
          <div class="milestone-progress">
            @foreach($project->milestones as $milestone)
              <div class="milestone-item">
                <input type="checkbox" class="milestone-checkbox form-check-input"
                       {{ $milestone->is_completed ? 'checked' : '' }}
                       onchange="toggleMilestone({{ $milestone->id }}, this.checked)">
                <span class="milestone-name {{ $milestone->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                  {{ $milestone->name }}
                </span>
                @if($milestone->target_date)
                  <small class="milestone-date">{{ $milestone->target_date->format('d M') }}</small>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!-- Kanban Board -->
    <div class="row">
      <div class="col-12" style="padding: 0 15px;">
        <div class="card">
          <div class="card-body" style="padding: 20px; overflow-x: scroll !important; overflow-y: hidden !important; -webkit-overflow-scrolling: touch;">
            <h6 class="mb-3">Board Columns: <span id="column-counter"></span></h6>
            <div class="kanban-board" id="kanban-board" style="width: max-content !important;">
              @foreach($board->columns as $column)
                <div class="kanban-column" data-column-id="{{ $column->id }}">
                  <div class="kanban-column-header" style="border-top-color: {{ $column->color }}">
                    <h6 class="kanban-column-title">{{ $column->name }}</h6>
                    <span class="kanban-column-count">{{ $column->cards->count() }}</span>
                  </div>

            <div class="kanban-cards">
              @foreach($column->cards as $card)
                <div class="kanban-card" data-card-id="{{ $card->id }}" draggable="true"
                     style="border-left-color: {{ $column->color }}">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="kanban-card-title mb-0">{{ $card->title }}</h6>
                    @if($card->priority)
                      @php
                        $priorityColors = [
                          'low' => 'info',
                          'medium' => 'warning',
                          'high' => 'danger',
                          'urgent' => 'danger'
                        ];
                        $priorityIcons = [
                          'low' => 'arrow-down',
                          'medium' => 'minus',
                          'high' => 'arrow-up',
                          'urgent' => 'alert-circle'
                        ];
                      @endphp
                      <span class="badge badge-{{ $priorityColors[$card->priority] ?? 'secondary' }}"
                            style="font-size: 10px; padding: 4px 8px;">
                        <i data-feather="{{ $priorityIcons[$card->priority] ?? 'flag' }}" style="width: 12px; height: 12px;"></i>
                        {{ ucfirst($card->priority) }}
                      </span>
                    @endif
                  </div>

                    @if($card->description)
                      <p class="text-muted small mb-2" style="font-size: 12px; line-height: 1.5;">
                        {{ Str::limit($card->description, 100) }}
                      </p>
                    @endif

                    @if($card->labels && count($card->labels) > 0)
                      <div class="mb-2">
                        @foreach($card->labels as $label)
                          <span class="badge badge-light-info me-1" style="font-size: 10px;">{{ $label }}</span>
                        @endforeach
                      </div>
                    @endif

                    <div class="kanban-card-meta">
                      @if($card->assignee)
                        <div class="kanban-card-assignee">
                          <div class="kanban-card-avatar">
                            {{ strtoupper(substr($card->assignee->name, 0, 2)) }}
                          </div>
                          <span>{{ Str::limit($card->assignee->name, 15) }}</span>
                        </div>
                      @endif

                      @if($card->due_date)
                        <div class="kanban-card-due {{ $card->is_overdue ? 'overdue' : '' }}">
                          <i data-feather="calendar" style="width: 12px; height: 12px;"></i>
                          {{ $card->due_date->format('d M') }}
                        </div>
                      @endif
                    </div>

                    @if($card->progress > 0)
                      <div class="kanban-card-progress">
                        <div class="progress-text">
                          <span>Progress</span>
                          <span>{{ round($card->progress) }}%</span>
                        </div>
                        <div class="progress" style="height: 4px; border-radius: 4px;">
                          <div class="progress-bar" role="progressbar"
                               style="width: {{ $card->progress }}%; background: {{ $column->color }};"></div>
                        </div>
                      </div>
                    @endif
                  </div>
                @endforeach

                <button class="add-card-btn" onclick="showAddCardModal({{ $column->id }}, '{{ $column->name }}')">
                  <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                  Add Card
                </button>
              </div>
            </div>
          @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Count and display columns
    document.addEventListener('DOMContentLoaded', function() {
      const columns = document.querySelectorAll('.kanban-column');
      document.getElementById('column-counter').textContent = columns.length + ' columns loaded';
      console.log('Kanban columns:', columns.length);
    });
  </script>

  <!-- Add Card Modal -->
  <div class="modal fade" id="addCardModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Card to <span id="modal-column-name"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="addCardForm" method="POST" action="{{ route('projects.cards.store', $project) }}">
          @csrf
          <input type="hidden" name="column_id" id="modal-column-id">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Priority</label>
                <select class="form-select" name="priority">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Due Date</label>
                <input type="date" class="form-control" name="due_date">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Assign To</label>
              <select class="form-select" name="assignee_id">
                <option value="">-- Unassigned --</option>
                @foreach($users as $user)
                  <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Card</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
const addCardModal = new bootstrap.Modal(document.getElementById('addCardModal'));

function showAddCardModal(columnId, columnName) {
    document.getElementById('modal-column-id').value = columnId;
    document.getElementById('modal-column-name').textContent = columnName;
    addCardModal.show();
}

function toggleMilestone(milestoneId, isCompleted) {
    fetch(`/api/milestones/${milestoneId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ is_completed: isCompleted })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Milestone updated');
    })
    .catch(error => console.error('Error:', error));
}

// Drag and Drop
let draggedCard = null;
let draggedFromColumn = null;

document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', function(e) {
        draggedCard = this;
        draggedFromColumn = this.closest('.kanban-column');
        this.classList.add('dragging');
    });

    card.addEventListener('dragend', function(e) {
        this.classList.remove('dragging');
    });

    card.addEventListener('click', function(e) {
        if (!e.target.closest('button')) {
            // You can implement card detail modal here
            // window.location.href = `/projects/{{ $project->id }}/cards/${this.dataset.cardId}`;
        }
    });
});

document.querySelectorAll('.kanban-cards').forEach(cards => {
    cards.addEventListener('dragover', function(e) {
        e.preventDefault();
        const column = this.closest('.kanban-column');
        column.style.background = '#e9ecef';
    });

    cards.addEventListener('dragleave', function(e) {
        const column = this.closest('.kanban-column');
        column.style.background = '';
    });

    cards.addEventListener('drop', function(e) {
        e.preventDefault();
        const column = this.closest('.kanban-column');
        column.style.background = '';

        if (draggedCard) {
            const targetColumn = this.closest('.kanban-column');
            const targetColumnId = targetColumn.dataset.columnId;
            const cardId = draggedCard.dataset.cardId;

            // Calculate position
            const cards = Array.from(this.querySelectorAll('.kanban-card'));
            const targetPosition = cards.length;

            // Move card in DOM
            this.insertBefore(draggedCard, this.querySelector('.add-card-btn'));

            // Update server
            fetch(`/projects/{{ $project->id }}/boards/{{ $board->id }}/cards/${cardId}/move`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    target_column_id: targetColumnId,
                    target_position: targetPosition
                })
            })
            .then(response => response.json())
            .then(data => {
                // Update column counts
                updateColumnCounts();

                // Show success feedback
                showToast('Card moved successfully', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to move card', 'error');

                // Revert DOM change
                draggedFromColumn.querySelector('.kanban-cards').insertBefore(draggedCard,
                    draggedFromColumn.querySelector('.add-card-btn'));
                updateColumnCounts();
            });
        }
    });
});

function updateColumnCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const count = column.querySelectorAll('.kanban-card').length;
        column.querySelector('.kanban-column-count').textContent = count;
    });
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `<i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Feather icons refresh
setTimeout(() => {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}, 100);
</script>
@endpush
