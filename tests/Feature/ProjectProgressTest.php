<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectProgressTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function makeCompany(): Company
    {
        return Company::factory()->create();
    }

    private function makeProject(Company $company): Project
    {
        return Project::factory()->create(['company_id' => $company->id]);
    }

    private function makeTask(Project $project, string $status = 'todo'): Task
    {
        return $project->tasks()->create([
            'company_id' => $project->company_id,
            'title'      => 'Task ' . uniqid(),
            'status'     => $status,
            'progress'   => $status === 'done' ? 100 : 0,
        ]);
    }

    // ---------------------------------------------------------------
    // Test: no tasks => progress 0
    // ---------------------------------------------------------------

    #[Test]
    public function project_with_no_tasks_has_progress_zero(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $this->assertEquals(0, $project->fresh()->progress_percent);
    }

    // ---------------------------------------------------------------
    // Test: creating a non-done task recalculates (goes to 0 from 0)
    // ---------------------------------------------------------------

    #[Test]
    public function creating_task_recalculates_progress(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        // 1 done out of 1 total => 100%
        $this->makeTask($project, 'done');
        $this->assertEquals(100, $project->fresh()->progress_percent);

        // Add a non-done task: 1 done out of 2 => 50%
        $this->makeTask($project, 'todo');
        $this->assertEquals(50, $project->fresh()->progress_percent);
    }

    // ---------------------------------------------------------------
    // Test: acceptance criteria — partial completion
    // 4 tasks, done 1 => 25%, done 2 => 50%
    // ---------------------------------------------------------------

    #[Test]
    public function four_tasks_one_done_gives_25_percent(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $tasks = [];
        for ($i = 0; $i < 4; $i++) {
            $tasks[] = $this->makeTask($project, 'todo');
        }

        // Move first task to done
        $tasks[0]->update(['status' => 'done']);

        $this->assertEquals(25, $project->fresh()->progress_percent);
    }

    #[Test]
    public function four_tasks_two_done_gives_50_percent(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $tasks = [];
        for ($i = 0; $i < 4; $i++) {
            $tasks[] = $this->makeTask($project, 'todo');
        }

        $tasks[0]->update(['status' => 'done']);
        $tasks[1]->update(['status' => 'done']);

        $this->assertEquals(50, $project->fresh()->progress_percent);
    }

    // ---------------------------------------------------------------
    // Test: acceptance criteria — full completion
    // 4 tasks, done 4 => 100%
    // ---------------------------------------------------------------

    #[Test]
    public function four_tasks_all_done_gives_100_percent(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $tasks = [];
        for ($i = 0; $i < 4; $i++) {
            $tasks[] = $this->makeTask($project, 'todo');
        }

        foreach ($tasks as $task) {
            $task->update(['status' => 'done']);
        }

        $this->assertEquals(100, $project->fresh()->progress_percent);
    }

    // ---------------------------------------------------------------
    // Test: reopen task from done decreases progress
    // ---------------------------------------------------------------

    #[Test]
    public function reopen_task_from_done_decreases_progress(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        // 4 tasks all done => 100%
        $tasks = [];
        for ($i = 0; $i < 4; $i++) {
            $tasks[] = $this->makeTask($project, 'done');
        }
        $this->assertEquals(100, $project->fresh()->progress_percent);

        // Reopen one => 75%
        $tasks[0]->update(['status' => 'todo']);
        $this->assertEquals(75, $project->fresh()->progress_percent);
    }

    // ---------------------------------------------------------------
    // Test: deleting a task recalculates progress
    // ---------------------------------------------------------------

    #[Test]
    public function deleting_done_task_recalculates_progress(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $done    = $this->makeTask($project, 'done');
        $nonDone = $this->makeTask($project, 'todo');

        // 1 done / 2 total = 50%
        $this->assertEquals(50, $project->fresh()->progress_percent);

        // Delete the done task => 0 done / 1 total = 0%
        $done->delete();
        $this->assertEquals(0, $project->fresh()->progress_percent);
    }

    // ---------------------------------------------------------------
    // Test: non-done status change does NOT trigger recalculate
    // (todo → doing does not change done count)
    // Verify by checking the observer does not fire unnecessarily.
    // We indirectly verify: progress stays the same.
    // ---------------------------------------------------------------

    #[Test]
    public function status_change_between_non_done_statuses_does_not_affect_progress(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $this->makeTask($project, 'done'); // 1 done
        $task = $this->makeTask($project, 'todo'); // 0 done non-done

        // 1 done / 2 = 50%
        $this->assertEquals(50, $project->fresh()->progress_percent);

        // Move todo → doing (non-done → non-done: progress should stay 50%)
        $task->update(['status' => 'doing']);
        $this->assertEquals(50, $project->fresh()->progress_percent);
    }

    // ---------------------------------------------------------------
    // Test: kanban view shows project progress_percent
    // ---------------------------------------------------------------

    #[Test]
    public function kanban_view_shows_project_progress(): void
    {
        $company = $this->makeCompany();
        $manager = User::factory()->create(['company_id' => $company->id, 'role' => 'employee']);
        $project = Project::factory()->create([
            'company_id'         => $company->id,
            'project_manager_id' => $manager->id,
        ]);

        // 2 tasks: 1 done => 50%
        $this->makeTask($project, 'done');
        $this->makeTask($project, 'todo');

        $project->refresh();

        $this->actingAs($manager)
            ->get(route('projects.kanban', $project))
            ->assertOk()
            ->assertSee('50%');
    }
}
