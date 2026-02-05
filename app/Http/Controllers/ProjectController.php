<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['manager', 'board'])
            ->where('company_id', auth()->user()->company_id)
            ->latest()
            ->paginate(15);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        $managers = User::where('company_id', auth()->user()->company_id)
            ->whereIn('role', ['admin', 'manager'])
            ->get();

        return view('projects.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        $project = Project::create($validated);

        return redirect()->route('projects.board', $project)
            ->with('success', 'Project berhasil dibuat dengan board dan milestones.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $managers = User::where('company_id', $project->company_id)
            ->whereIn('role', ['admin', 'manager'])
            ->get();

        return view('projects.edit', compact('project', 'managers'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project berhasil diupdate.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil dihapus.');
    }

    public function board(Project $project)
    {
        $this->authorize('view', $project);

        $board = $project->board()->with(['columns.cards.assignee', 'columns.cards.checklists'])->first();
        $users = User::where('company_id', $project->company_id)->get();

        return view('projects.board', compact('project', 'board', 'users'));
    }
}
