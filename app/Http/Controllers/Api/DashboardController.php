<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get portfolio dashboard summary.
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        // Total projects
        $totalProjects = Project::where('company_id', $companyId)->count();

        // Active projects
        $activeProjects = Project::where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        // Average progress per portfolio
        $avgProgress = Project::where('company_id', $companyId)
            ->where('status', 'active')
            ->get()
            ->map(function ($project) {
                // Get all cards for this project
                $cards = Card::whereHas('column.board', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })->get();

                if ($cards->isEmpty()) {
                    return 0;
                }

                return $cards->avg('progress');
            })
            ->avg();

        // Tasks by status (using column names as proxy for status)
        $tasksByStatus = Card::select('columns.name as status', DB::raw('count(*) as count'))
            ->join('columns', 'cards.column_id', '=', 'columns.id')
            ->join('boards', 'columns.board_id', '=', 'boards.id')
            ->join('projects', 'boards.project_id', '=', 'projects.id')
            ->where('projects.company_id', $companyId)
            ->groupBy('columns.name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });

        // Overdue tasks count
        $overdueTasks = Card::whereHas('column.board.project', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('progress', '<', 100)
            ->count();

        // Tasks due soon (within 48 hours)
        $tasksDueSoon = Card::whereHas('column.board.project', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addHours(48)])
            ->where('progress', '<', 100)
            ->count();

        // Top 5 projects by pending tasks
        $topProjects = Project::where('company_id', $companyId)
            ->with('board')
            ->get()
            ->map(function ($project) {
                $pendingTasks = Card::whereHas('column.board', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })->where('progress', '<', 100)->count();

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'pending_tasks' => $pendingTasks,
                ];
            })
            ->sortByDesc('pending_tasks')
            ->take(5)
            ->values();

        // My assigned tasks
        $myTasks = Card::where('assignee_id', $request->user()->id)
            ->whereHas('column.board.project', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->with(['column', 'column.board.project'])
            ->where('progress', '<', 100)
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'title' => $card->title,
                    'project' => $card->column->board->project->name,
                    'due_date' => $card->due_date?->format('Y-m-d'),
                    'priority' => $card->priority,
                    'progress' => (float) $card->progress,
                    'is_overdue' => $card->is_overdue,
                ];
            });

        return response()->json([
            'summary' => [
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'avg_progress' => round($avgProgress ?? 0, 2),
                'overdue_tasks' => $overdueTasks,
                'tasks_due_soon' => $tasksDueSoon,
            ],
            'tasks_by_status' => $tasksByStatus,
            'top_projects' => $topProjects,
            'my_tasks' => $myTasks,
        ]);
    }
}
