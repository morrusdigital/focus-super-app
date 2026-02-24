{{-- Shared form partial used by create.blade.php and edit.blade.php --}}
{{-- Variables expected: $project, $members, $assigneeIds (array of selected user IDs) --}}
{{-- When on edit, $task is also expected for old-value fallback --}}

<div class="row g-3">
    {{-- Title --}}
    <div class="col-md-8">
        <label class="form-label">Judul Task <span class="text-danger">*</span></label>
        <input class="form-control @error('title') is-invalid @enderror"
               name="title" type="text"
               value="{{ old('title', $task->title ?? '') }}" required maxlength="255">
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Status --}}
    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror"
                name="status" id="task-status" required>
            @foreach (\App\Enums\TaskStatus::cases() as $s)
                <option value="{{ $s->value }}"
                    {{ old('status', $task->status->value ?? 'todo') === $s->value ? 'selected' : '' }}>
                    {{ $s->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Progress --}}
    <div class="col-md-4">
        <label class="form-label">Progress (0–100) <span class="text-danger">*</span></label>
        <input class="form-control @error('progress') is-invalid @enderror"
               name="progress" id="task-progress" type="number" min="0" max="100"
               value="{{ old('progress', $task->progress ?? 0) }}" required>
        @error('progress')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Due Date --}}
    <div class="col-md-4">
        <label class="form-label">Due Date</label>
        <input class="form-control @error('due_date') is-invalid @enderror"
               name="due_date" type="date"
               value="{{ old('due_date', isset($task) ? $task->due_date?->format('Y-m-d') : '') }}">
        @error('due_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Blocked Reason — shown only when status = blocked --}}
    <div class="col-md-12" id="blocked-reason-wrapper"
         style="{{ old('status', $task->status->value ?? 'todo') === 'blocked' ? '' : 'display:none' }}">
        <label class="form-label">Blocked Reason <span class="text-danger">*</span></label>
        <textarea class="form-control @error('blocked_reason') is-invalid @enderror"
                  name="blocked_reason" rows="2">{{ old('blocked_reason', $task->blocked_reason ?? '') }}</textarea>
        @error('blocked_reason')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Assignees --}}
    <div class="col-md-12">
        <label class="form-label">Assignees <span class="text-danger">*</span> <small class="text-muted">(pilih satu atau lebih)</small></label>
        @if ($members->isEmpty())
            <div class="alert alert-warning mb-0">Belum ada member project yang dapat ditugaskan. <a href="{{ route('projects.show', $project) }}">Tambah member dulu.</a></div>
        @else
            <select class="form-select @error('assignees') is-invalid @enderror @error('assignees.*') is-invalid @enderror"
                    name="assignees[]" multiple required size="{{ min(6, $members->count()) }}">
                @foreach ($members as $member)
                    <option value="{{ $member->id }}"
                        {{ in_array($member->id, (array) $assigneeIds) ? 'selected' : '' }}>
                        {{ $member->name }} ({{ $member->role }})
                    </option>
                @endforeach
            </select>
            @error('assignees')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @error('assignees.*')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            <small class="text-muted">Tahan Ctrl/Cmd untuk memilih beberapa.</small>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect  = document.getElementById('task-status');
        const progressInput = document.getElementById('task-progress');
        const blockedWrapper = document.getElementById('blocked-reason-wrapper');

        function syncRules() {
            const status   = statusSelect.value;
            const progress = parseInt(progressInput.value, 10);

            // Show/hide blocked reason
            blockedWrapper.style.display = status === 'blocked' ? '' : 'none';

            // Auto-set progress when done is selected
            if (status === 'done' && progress !== 100) {
                progressInput.value = 100;
            }
        }

        // Auto-set status to done when progress reaches 100
        progressInput.addEventListener('change', function () {
            if (parseInt(this.value, 10) === 100) {
                statusSelect.value = 'done';
                blockedWrapper.style.display = 'none';
            }
        });

        statusSelect.addEventListener('change', syncRules);
        syncRules();
    });
</script>
@endpush
