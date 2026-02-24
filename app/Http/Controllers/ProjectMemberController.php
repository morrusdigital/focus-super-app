<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectMemberRequest;
use App\Models\Project;
use App\Models\User;

class ProjectMemberController extends Controller
{
    public function store(StoreProjectMemberRequest $request, Project $project)
    {
        $project->members()->attach($request->validated()['user_id']);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Member berhasil ditambahkan.');
    }

    public function destroy(Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        $project->members()->detach($user->id);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Member berhasil dihapus.');
    }
}
