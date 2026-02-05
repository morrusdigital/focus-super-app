<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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
        ]);
    }

    public function test_can_get_dashboard_summary(): void
    {
        // Create projects
        $project1 = Project::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'active',
        ]);

        $project2 = Project::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'active',
        ]);

        // Create cards with different statuses
        $column1 = $project1->board->columns->first();
        $doneColumn = $project1->board->columns->where('name', 'Done')->first();

        Card::factory()->create([
            'column_id' => $column1->id,
            'assignee_id' => $this->user->id,
            'progress' => 50,
        ]);

        Card::factory()->create([
            'column_id' => $doneColumn->id,
            'progress' => 100,
        ]);

        // Create overdue card
        Card::factory()->create([
            'column_id' => $column1->id,
            'due_date' => now()->subDays(2),
            'progress' => 30,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'summary' => [
                'total_projects',
                'active_projects',
                'avg_progress',
                'overdue_tasks',
                'tasks_due_soon',
            ],
            'tasks_by_status',
            'top_projects',
            'my_tasks',
        ]);

        $response->assertJson([
            'summary' => [
                'total_projects' => 2,
                'active_projects' => 2,
                'overdue_tasks' => 1,
            ],
        ]);
    }
}
