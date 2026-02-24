<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    /**
     * GET /projects/{project}/kanban
     *
     * Display a read-only Kanban board for the project.
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
}
