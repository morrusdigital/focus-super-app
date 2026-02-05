<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::with(['manager', 'board', 'milestones'])
            ->where('company_id', $request->user()->company_id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->latest()->paginate(15);

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            'company_id' => $request->user()->company_id,
            'name' => $request->name,
            'description' => $request->description,
            'manager_id' => $request->manager_id ?? $request->user()->id,
            'status' => $request->status ?? 'planning',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'budget' => $request->budget,
        ]);

        return new ProjectResource($project->load(['manager', 'board', 'milestones']));
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load(['manager', 'board.columns.cards.assignee', 'milestones']));
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return new ProjectResource($project->load(['manager', 'board', 'milestones']));
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
