<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Card;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        // Total projects
        $totalProjects = Project::where('company_id', $companyId)->count();

        // Projects by status
        $projectsByStatus = Project::where('company_id', $companyId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Total cards/tasks
        $totalCards = Card::whereHas('column.board.project', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->count();

        // Cards by column (to get Done count)
        $cardsByColumn = Card::whereHas('column.board.project', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->join('columns', 'cards.column_id', '=', 'columns.id')
            ->selectRaw('columns.name, count(*) as count')
            ->groupBy('columns.name')
            ->pluck('count', 'name')
            ->toArray();

        // Overdue tasks
        $overdueTasks = Card::whereHas('column.board.project', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->where('due_date', '<', now())
            ->whereNotIn('column_id', function($q) {
                $q->select('id')->from('columns')->where('name', 'Done');
            })
            ->count();

        // Average project progress (from cards)
        $projects = Project::where('company_id', $companyId)
            ->with(['board.columns.cards'])
            ->get();

        $avgProgress = 0;
        if ($projects->count() > 0) {
            $totalProgress = 0;
            $projectCount = 0;

            foreach ($projects as $project) {
                if ($project->board && $project->board->columns->count() > 0) {
                    $totalCards = 0;
                    $doneCards = 0;

                    foreach ($project->board->columns as $column) {
                        $cardsCount = $column->cards->count();
                        $totalCards += $cardsCount;

                        if ($column->name === 'Done') {
                            $doneCards = $cardsCount;
                        }
                    }

                    if ($totalCards > 0) {
                        $totalProgress += ($doneCards / $totalCards) * 100;
                        $projectCount++;
                    }
                }
            }

            if ($projectCount > 0) {
                $avgProgress = round($totalProgress / $projectCount, 1);
            }
        }

        // Top projects (by card completion)
        $topProjects = $projects->map(function($project) {
            if ($project->board && $project->board->columns->count() > 0) {
                $totalCards = 0;
                $doneCards = 0;

                foreach ($project->board->columns as $column) {
                    $cardsCount = $column->cards->count();
                    $totalCards += $cardsCount;

                    if ($column->name === 'Done') {
                        $doneCards = $cardsCount;
                    }
                }

                $progress = $totalCards > 0 ? round(($doneCards / $totalCards) * 100, 1) : 0;

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'progress' => $progress,
                    'total_cards' => $totalCards,
                    'done_cards' => $doneCards,
                ];
            }

            return null;
        })->filter()->sortByDesc('progress')->take(5)->values();

        return view('dashboard.index', compact(
            'totalProjects',
            'projectsByStatus',
            'totalCards',
            'cardsByColumn',
            'overdueTasks',
            'avgProgress',
            'topProjects'
        ));
    }
}
