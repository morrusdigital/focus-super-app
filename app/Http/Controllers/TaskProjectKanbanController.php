<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\TaskProject\MoveTaskProjectTaskRequest;
use App\Models\TaskProject;
use App\Models\TaskProjectTask;

class TaskProjectKanbanController extends Controller
{
    /**
     * GET /task-projects/{taskProject}/kanban
     *
     * Display the Kanban board for a Task Project.
     */
    public function show(TaskProject $taskProject)
    {
        $this->authorize('viewKanban', $taskProject);

        $tasks = $taskProject->tasks()
            ->with('assignees')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        // Group into 4-column structure
        $grouped = $tasks->groupBy(fn ($t) => $t->status->value);

        $columns = [];
        foreach (TaskStatus::cases() as $status) {
            $columns[$status->value] = [
                'label' => $status->label(),
                'tasks' => $grouped->get($status->value, collect()),
            ];
        }

        $summary = $taskProject->summary();

        return view('task-projects.kanban', compact('taskProject', 'columns', 'summary'));
    }

    /**
     * PATCH /task-projects/{taskProject}/tasks/{task}/move
     *
     * Move a task to a new status. Business rules applied here in addition
     * to the model boot() hook.
     *
     * Authorization: via MoveTaskProjectTaskRequest (moveStatus policy).
     */
    public function move(MoveTaskProjectTaskRequest $request, TaskProject $taskProject, TaskProjectTask $task)
    {
        abort_unless($task->task_project_id === $taskProject->id, 404);

        $status        = $request->validated()['status'];
        $blockedReason = $request->validated()['blocked_reason'] ?? null;

        // Business rule: moving to done → progress = 100
        $progress = $status === TaskStatus::Done->value ? 100 : $task->progress;

        // Business rule: not blocked → clear reason
        if ($status !== TaskStatus::Blocked->value) {
            $blockedReason = null;
        }

        $task->update([
            'status'         => $status,
            'progress'       => $progress,
            'blocked_reason' => $blockedReason,
        ]);

        return redirect()
            ->route('task-projects.kanban', $taskProject)
            ->with('status', 'Task dipindahkan ke ' . TaskStatus::from($status)->label() . '.');
    }
}
