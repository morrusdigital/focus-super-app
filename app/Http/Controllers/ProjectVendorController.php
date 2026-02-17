<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectVendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectVendorController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $vendors = $project->vendors()
            ->orderBy('name')
            ->get();

        if (request()->expectsJson()) {
            return response()->json($vendors);
        }

        return redirect()->route('projects.show', $project);
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('manageVendors', $project);

        $data = $this->validatePayload($request, $project);

        $project->vendors()->create([
            'name' => $data['name'],
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Vendor project berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectVendor $vendor)
    {
        $this->authorize('manageVendors', $project);
        $this->assertVendorBelongsToProject($project, $vendor);

        $data = $this->validatePayload($request, $project, $vendor);

        $vendor->update([
            'name' => $data['name'],
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Vendor project berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectVendor $vendor)
    {
        $this->authorize('manageVendors', $project);
        $this->assertVendorBelongsToProject($project, $vendor);

        $vendor->delete();

        return redirect()->route('projects.show', $project)->with('status', 'Vendor project berhasil dihapus.');
    }

    private function validatePayload(Request $request, Project $project, ?ProjectVendor $vendor = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_vendors', 'name')
                    ->where(fn ($query) => $query->where('project_id', $project->id))
                    ->ignore($vendor?->id),
            ],
        ]);
    }

    private function assertVendorBelongsToProject(Project $project, ProjectVendor $vendor): void
    {
        if ((int) $project->id !== (int) $vendor->project_id) {
            abort(404);
        }
    }
}
