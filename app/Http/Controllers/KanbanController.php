<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\MoveTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    /**
     * GET /projects/{project}/kanban
     *
     * Display a Kanban board for the project.
     * Tasks are loaded in a single query (with assignees eager-loaded),
     * then grouped by status in PHP — no N+1.
     *
     * Authorization: user must be able to `view` the project.
     */
    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        // Single query — load all tasks with their assignees
        $tasks = $project->tasks()
            ->with('assignees')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        // Group by status value; ensure all 4 columns always exist
        $grouped = $tasks->groupBy(fn ($t) => $t->status->value);

        $columns = [];
        foreach (TaskStatus::cases() as $status) {
            $columns[$status->value] = [
                'label' => $status->label(),
                'tasks' => $grouped->get($status->value, collect()),
            ];
        }

        return view('tasks.kanban', compact('project', 'columns'));
    }

    // ---------------------------------------------------------------
    // PATCH /tasks/{task}/move
    //
    // Move a task to a new status column.
    // Business rules:
    //   - move to done   → progress forced to 100
    //   - move to non-blocked → clear blocked_reason
    //   - blocked_reason required when moving to blocked (validated in request)
    //
    // Authorization: via MoveTaskRequest (update OR markDone for done).
    // Redirects back to the project kanban board.
    // ---------------------------------------------------------------

    public function move(MoveTaskRequest $request, Task $task)
    {
        $status        = $request->validated()['status'];
        $blockedReason = $request->validated()['blocked_reason'] ?? null;

        // Business rule: moving to done forces progress = 100
        $progress = $status === TaskStatus::Done->value
            ? 100
            : $task->progress;

        // Business rule: not moving to blocked → clear blocked_reason
        if ($status !== TaskStatus::Blocked->value) {
            $blockedReason = null;
        }

        $task->update([
            'status'         => $status,
            'progress'       => $progress,
            'blocked_reason' => $blockedReason,
        ]);

        // Redirect back to the project's kanban board
        return redirect()
            ->route('projects.kanban', $task->project_id)
            ->with('status', 'Task dipindahkan ke ' . TaskStatus::from($status)->label() . '.');
    }
}
