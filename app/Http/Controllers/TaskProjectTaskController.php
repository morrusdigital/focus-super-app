<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskProject\StoreTaskProjectTaskRequest;
use App\Http\Requests\TaskProject\UpdateTaskProjectTaskRequest;
use App\Models\TaskProject;
use App\Models\TaskProjectTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskProjectTaskController extends Controller
{
    // ---------------------------------------------------------------
    // index — list tasks for a task project
    // ---------------------------------------------------------------

    public function index(TaskProject $taskProject)
    {
        $this->authorize('view', $taskProject);

        $tasks = $taskProject->tasks()
            ->with('assignees')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $summary = $taskProject->summary();

        return view('task-projects.tasks.index', compact('taskProject', 'tasks', 'summary'));
    }

    // ---------------------------------------------------------------
    // create / store
    // ---------------------------------------------------------------

    public function create(TaskProject $taskProject)
    {
        $this->authorize('manageTasks', $taskProject);

        $users = User::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return view('task-projects.tasks.create', compact('taskProject', 'users'));
    }

    public function store(StoreTaskProjectTaskRequest $request, TaskProject $taskProject)
    {
        $this->authorize('manageTasks', $taskProject);

        $data = $request->validated();

        /** @var TaskProjectTask $task */
        $task = $taskProject->tasks()->create([
            'company_id'     => $taskProject->company_id,
            'title'          => $data['title'],
            'status'         => $data['status'],
            'progress'       => $data['progress'],
            'blocked_reason' => $data['blocked_reason'] ?? null,
            'due_date'       => $data['due_date'] ?? null,
        ]);

        if (! empty($data['assignees'])) {
            $task->assignees()->sync($data['assignees']);
        }

        return redirect()
            ->route('task-projects.tasks.index', $taskProject)
            ->with('success', 'Task berhasil ditambahkan.');
    }

    // ---------------------------------------------------------------
    // edit / update
    // ---------------------------------------------------------------

    public function edit(TaskProject $taskProject, TaskProjectTask $task)
    {
        $this->authorize('update', $task);
        abort_unless($task->task_project_id === $taskProject->id, 404);

        $users = User::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        $assigneeIds = $task->assignees()->pluck('users.id')->toArray();

        return view('task-projects.tasks.edit', compact('taskProject', 'task', 'users', 'assigneeIds'));
    }

    public function update(UpdateTaskProjectTaskRequest $request, TaskProject $taskProject, TaskProjectTask $task)
    {
        $this->authorize('update', $task);
        abort_unless($task->task_project_id === $taskProject->id, 404);

        $data = $request->validated();

        $task->update([
            'title'          => $data['title'],
            'status'         => $data['status'],
            'progress'       => $data['progress'],
            'blocked_reason' => $data['blocked_reason'] ?? null,
            'due_date'       => $data['due_date'] ?? null,
        ]);

        if (isset($data['assignees'])) {
            $task->assignees()->sync($data['assignees']);
        }

        return redirect()
            ->route('task-projects.tasks.index', $taskProject)
            ->with('success', 'Task berhasil diperbarui.');
    }

    // ---------------------------------------------------------------
    // destroy
    // ---------------------------------------------------------------

    public function destroy(TaskProject $taskProject, TaskProjectTask $task)
    {
        $this->authorize('delete', $task);
        abort_unless($task->task_project_id === $taskProject->id, 404);

        $task->delete();

        return redirect()
            ->route('task-projects.tasks.index', $taskProject)
            ->with('success', 'Task berhasil dihapus.');
    }
}
