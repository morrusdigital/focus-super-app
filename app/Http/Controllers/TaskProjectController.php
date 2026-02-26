<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskProject\StoreTaskProjectRequest;
use App\Http\Requests\TaskProject\UpdateTaskProjectRequest;
use App\Models\TaskProject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskProjectController extends Controller
{
    // ---------------------------------------------------------------
    // index — list all task projects visible to the authenticated user
    // ---------------------------------------------------------------

    public function index(Request $request)
    {
        $this->authorize('viewAny', TaskProject::class);

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $query = TaskProject::with(['projectManager']);

        // Scope by company (holding_admin / finance_holding see all)
        if (! $user->isHoldingAdmin() && ! $user->isFinanceHolding()) {
            $query->where('company_id', $user->company_id);
        }

        // Employee: further filter to only projects they are involved in
        if ($user->isEmployee()) {
            $query->where(function ($q) use ($user) {
                $q->where('project_manager_id', $user->id)
                  ->orWhere('created_by', $user->id)
                  ->orWhereHas('tasks.assignees', fn ($s) => $s->where('users.id', $user->id));
            });
        }

        $taskProjects = $query->orderByDesc('created_at')->get();

        return view('task-projects.index', compact('taskProjects'));
    }

    // ---------------------------------------------------------------
    // show — detail with summary
    // ---------------------------------------------------------------

    public function show(TaskProject $taskProject)
    {
        $this->authorize('view', $taskProject);

        $summary = $taskProject->summary();

        return view('task-projects.show', compact('taskProject', 'summary'));
    }

    // ---------------------------------------------------------------
    // create / store
    // ---------------------------------------------------------------

    public function create()
    {
        $this->authorize('create', TaskProject::class);

        $managers = User::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return view('task-projects.create', compact('managers'));
    }

    public function store(StoreTaskProjectRequest $request)
    {
        $this->authorize('create', TaskProject::class);

        $user = Auth::user();

        $taskProject = TaskProject::create([
            'company_id'         => $user->company_id,
            'name'               => $request->validated('name'),
            'project_manager_id' => $request->validated('project_manager_id'),
            'created_by'         => $user->id,
        ]);

        return redirect()
            ->route('task-projects.show', $taskProject)
            ->with('success', 'Task Project berhasil dibuat.');
    }

    // ---------------------------------------------------------------
    // edit / update
    // ---------------------------------------------------------------

    public function edit(TaskProject $taskProject)
    {
        $this->authorize('update', $taskProject);

        $managers = User::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return view('task-projects.edit', compact('taskProject', 'managers'));
    }

    public function update(UpdateTaskProjectRequest $request, TaskProject $taskProject)
    {
        $this->authorize('update', $taskProject);

        $taskProject->update([
            'name'               => $request->validated('name'),
            'project_manager_id' => $request->validated('project_manager_id'),
        ]);

        return redirect()
            ->route('task-projects.show', $taskProject)
            ->with('success', 'Task Project berhasil diperbarui.');
    }

    // ---------------------------------------------------------------
    // destroy
    // ---------------------------------------------------------------

    public function destroy(TaskProject $taskProject)
    {
        $this->authorize('delete', $taskProject);

        $taskProject->delete();

        return redirect()
            ->route('task-projects.index')
            ->with('success', 'Task Project berhasil dihapus.');
    }
}
