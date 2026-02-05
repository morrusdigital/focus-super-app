<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Models\Project;

class BoardController extends Controller
{
    /**
     * Display the board for a project.
     */
    public function show(Project $project, Board $board)
    {
        $this->authorize('view', $board);

        return new BoardResource($board->load([
            'columns.cards' => function ($query) {
                $query->with(['assignee', 'checklists.items'])->orderBy('position');
            }
        ]));
    }
}
