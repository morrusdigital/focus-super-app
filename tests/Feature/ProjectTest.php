<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'manager',
        ]);
    }

    public function test_can_create_project(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/projects', [
            'name' => 'Test Project',
            'description' => 'Test Description',
            'status' => 'active',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'status',
                'board',
                'milestones',
            ],
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'company_id' => $this->company->id,
        ]);

        // Check that board was auto-created
        $project = Project::where('name', 'Test Project')->first();
        $this->assertNotNull($project->board);
        $this->assertCount(7, $project->board->columns); // 7 default columns

        // Check that milestones were auto-created
        $this->assertCount(5, $project->milestones); // 5 default milestones
    }

    public function test_can_list_projects(): void
    {
        Project::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/projects');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_view_project(): void
    {
        $project = Project::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'board' => [
                    'columns',
                ],
            ],
        ]);
    }

    public function test_can_update_project(): void
    {
        $project = Project::factory()->create([
            'company_id' => $this->company->id,
            'manager_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/projects/{$project->id}", [
            'name' => 'Updated Project',
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project',
            'status' => 'completed',
        ]);
    }

    public function test_can_delete_project(): void
    {
        $project = Project::factory()->create([
            'company_id' => $this->company->id,
            'manager_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }
}
