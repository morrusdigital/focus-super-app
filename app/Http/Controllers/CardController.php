<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'column_id' => 'required|exists:columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Calculate position (append to end)
        $position = Card::where('column_id', $validated['column_id'])->max('position') + 1;
        $validated['position'] = $position;

        Card::create($validated);

        return redirect()->route('projects.board', $project)
            ->with('success', 'Card berhasil ditambahkan.');
    }
}
