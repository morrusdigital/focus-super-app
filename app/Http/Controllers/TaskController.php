<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\PatchTaskStatusRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;

class TaskController extends Controller
{
    // ---------------------------------------------------------------
    // index — list tasks for a project
    // ---------------------------------------------------------------

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with('assignees')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        return view('tasks.index', compact('project', 'tasks'));
    }

    // ---------------------------------------------------------------
    // create / store
    // ---------------------------------------------------------------

    public function create(Project $project)
    {
        $this->authorize('update', $project);

        $members = $project->members()->orderBy('name')->get();

        return view('tasks.create', compact('project', 'members'));
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $data = $this->applyBusinessRules($request->validated());

        $task = $project->tasks()->create([
            'company_id'     => $project->company_id,
            'title'          => $data['title'],
            'status'         => $data['status'],
            'progress'       => $data['progress'],
            'due_date'       => $data['due_date'] ?? null,
            'blocked_reason' => $data['blocked_reason'] ?? null,
        ]);

        $task->assignees()->sync($data['assignees']);

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('status', 'Task berhasil dibuat.');
    }

    // ---------------------------------------------------------------
    // edit / update
    // ---------------------------------------------------------------

    public function edit(Project $project, Task $task)
    {
        $this->authorize('update', $task);
        $this->assertTaskBelongsToProject($project, $task);

        $members         = $project->members()->orderBy('name')->get();
        $assigneeIds     = $task->assignees->pluck('id')->toArray();

        return view('tasks.edit', compact('project', 'task', 'members', 'assigneeIds'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $this->assertTaskBelongsToProject($project, $task);

        $data = $this->applyBusinessRules($request->validated());

        $task->update([
            'title'          => $data['title'],
            'status'         => $data['status'],
            'progress'       => $data['progress'],
            'due_date'       => $data['due_date'] ?? null,
            'blocked_reason' => $data['blocked_reason'] ?? null,
        ]);

        $task->assignees()->sync($data['assignees']);

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('status', 'Task berhasil diperbarui.');
    }

    // ---------------------------------------------------------------
    // patchStatus — quick status/progress update (e.g. Kanban action)
    // ---------------------------------------------------------------

    public function patchStatus(PatchTaskStatusRequest $request, Project $project, Task $task)
    {
        $this->assertTaskBelongsToProject($project, $task);

        $data = $this->applyBusinessRules($request->validated());

        $task->update([
            'status'         => $data['status'],
            'progress'       => $data['progress'],
            'blocked_reason' => $data['blocked_reason'] ?? null,
        ]);

        return redirect()
            ->route('projects.tasks.index', $project)
            ->with('status', 'Status task berhasil diperbarui.');
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    /**
     * Apply bidirectional done/progress rules:
     *  - status done  → progress 100
     *  - progress 100 → status done
     *  - not blocked  → clear blocked_reason
     */
    private function applyBusinessRules(array $data): array
    {
        $status   = $data['status'] ?? null;
        $progress = isset($data['progress']) ? (int) $data['progress'] : null;

        if ($status === TaskStatus::Done->value) {
            $data['progress'] = 100;
        } elseif ($progress === 100) {
            $data['status'] = TaskStatus::Done->value;
        }

        if (($data['status'] ?? null) !== TaskStatus::Blocked->value) {
            $data['blocked_reason'] = null;
        }

        return $data;
    }

    private function assertTaskBelongsToProject(Project $project, Task $task): void
    {
        if ((int) $task->project_id !== (int) $project->id) {
            abort(404);
        }
    }
}
