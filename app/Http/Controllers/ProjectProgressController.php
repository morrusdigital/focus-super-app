<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectProgressController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $progresses = $project->progresses()
            ->with('creator')
            ->orderByDesc('progress_date')
            ->orderByDesc('id')
            ->get();

        if (request()->expectsJson()) {
            return response()->json($progresses);
        }

        return redirect()->route('projects.show', $project);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('manageProgress', $project);

        $data = $request->validate([
            'progress_date' => ['required', 'date'],
            'progress_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $project->progresses()->create([
            'progress_date' => $data['progress_date'],
            'progress_percent' => $data['progress_percent'],
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Progress kerja berhasil ditambahkan.');
    }
}
