<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectProgressFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_company_can_input_progress_and_see_history_delta(): void
    {
        $company = Company::create([
            'name' => 'Company Progress',
            'type' => 'company',
        ]);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin_company',
        ]);

        $project = Project::create([
            'company_id' => $company->id,
            'name' => 'Project Progress',
            'address' => 'Alamat',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        $this->actingAs($admin)->post(route('projects.progresses.store', $project), [
            'progress_date' => '2026-02-01',
            'progress_percent' => 20,
            'notes' => 'Progress awal',
        ])->assertRedirect(route('projects.show', $project));

        $this->actingAs($admin)->post(route('projects.progresses.store', $project), [
            'progress_date' => '2026-02-10',
            'progress_percent' => 35,
            'notes' => 'Progress naik',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_progresses', [
            'project_id' => $project->id,
            'progress_percent' => 20,
            'created_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('project_progresses', [
            'project_id' => $project->id,
            'progress_percent' => 35,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertOk();
        $response->assertSee('Progress Kerja');
        $response->assertSee('35,00%');
        $response->assertSee('20,00%');
        $response->assertSee('+15,00%');
        $response->assertSee('naik 15,00%');
    }

    public function test_finance_holding_can_view_progress_history_but_cannot_input(): void
    {
        $holding = Company::create([
            'name' => 'Holding Progress',
            'type' => 'holding',
        ]);

        $childCompany = Company::create([
            'name' => 'Child Progress',
            'type' => 'company',
            'parent_id' => $holding->id,
        ]);

        $admin = User::factory()->create([
            'company_id' => $childCompany->id,
            'role' => 'admin_company',
        ]);

        $finance = User::factory()->create([
            'company_id' => $holding->id,
            'role' => 'finance_holding',
        ]);

        $project = Project::create([
            'company_id' => $childCompany->id,
            'name' => 'Project Child',
            'address' => 'Alamat Child',
            'contract_value' => 100000000,
            'use_pph' => false,
            'use_ppn' => false,
        ]);

        ProjectProgress::create([
            'project_id' => $project->id,
            'progress_date' => '2026-02-01',
            'progress_percent' => 25,
            'notes' => 'Initial',
            'created_by' => $admin->id,
        ]);

        $viewResponse = $this->actingAs($finance)->get(route('projects.show', $project));
        $viewResponse->assertOk();
        $viewResponse->assertSee('25,00%');

        $storeResponse = $this->actingAs($finance)->post(route('projects.progresses.store', $project), [
            'progress_date' => '2026-02-12',
            'progress_percent' => 30,
        ]);
        $storeResponse->assertForbidden();
    }
}
