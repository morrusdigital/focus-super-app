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

class KanbanMoveTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function makeCompany(): Company
    {
        return Company::factory()->create();
    }

    private function makeUser(Company $company, string $role): User
    {
        return User::factory()->create(['company_id' => $company->id, 'role' => $role]);
    }

    private function makeProject(Company $company, ?User $manager = null): Project
    {
        return Project::factory()->create([
            'company_id'         => $company->id,
            'project_manager_id' => $manager?->id,
        ]);
    }

    private function makeTask(Project $project, string $status = 'doing', int $progress = 30): Task
    {
        return $project->tasks()->create([
            'company_id' => $project->company_id,
            'title'      => 'Test Task',
            'status'     => $status,
            'progress'   => $progress,
        ]);
    }

    // ---------------------------------------------------------------
    // Test Minimum: move doing → done
    // ---------------------------------------------------------------

    #[Test]
    public function move_doing_to_done_sets_progress_100(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);
        $task    = $this->makeTask($project, 'doing', 50);

        $this->actingAs($manager)
            ->patch(route('tasks.move', $task), ['status' => 'done'])
            ->assertRedirect(route('projects.kanban', $project));

        $this->assertDatabaseHas('tasks', [
            'id'       => $task->id,
            'status'   => 'done',
            'progress' => 100,
        ]);
    }

    // ---------------------------------------------------------------
    // Test Minimum: move doing → blocked tanpa reason fail
    // ---------------------------------------------------------------

    #[Test]
    public function move_to_blocked_without_reason_is_rejected(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);
        $task    = $this->makeTask($project, 'doing');

        $this->actingAs($manager)
            ->patch(route('tasks.move', $task), [
                'status'         => 'blocked',
                'blocked_reason' => '',
            ])
            ->assertSessionHasErrors('blocked_reason');

        // Status must NOT have changed
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'doing']);
    }

    // ---------------------------------------------------------------
    // Test Minimum: unauthorized move fail
    // ---------------------------------------------------------------

    #[Test]
    public function member_not_assignee_cannot_move_task(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $member  = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);

        $project->members()->attach($member->id);
        $task = $this->makeTask($project, 'doing');
        // member is NOT an assignee — cannot even markDone

        $this->actingAs($member)
            ->patch(route('tasks.move', $task), ['status' => 'done'])
            ->assertForbidden();
    }

    #[Test]
    public function user_from_different_company_cannot_move_task(): void
    {
        $companyA = $this->makeCompany();
        $companyB = $this->makeCompany();

        $managerA = $this->makeUser($companyA, 'employee');
        $managerB = $this->makeUser($companyB, 'employee');

        $projectA = $this->makeProject($companyA, $managerA);
        $projectB = $this->makeProject($companyB, $managerB);

        $taskA = $this->makeTask($projectA, 'doing');

        // managerB tries to move a task that belongs to company A
        $this->actingAs($managerB)
            ->patch(route('tasks.move', $taskA), ['status' => 'done'])
            ->assertForbidden();
    }

    #[Test]
    public function guest_cannot_move_task(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($project);

        $this->patch(route('tasks.move', $task), ['status' => 'done'])
            ->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Acceptance criteria: move to blocked with reason accepted
    // ---------------------------------------------------------------

    #[Test]
    public function move_to_blocked_with_reason_is_accepted(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);
        $task    = $this->makeTask($project, 'doing');

        $this->actingAs($manager)
            ->patch(route('tasks.move', $task), [
                'status'         => 'blocked',
                'blocked_reason' => 'Menunggu approval klien',
            ])
            ->assertRedirect(route('projects.kanban', $project));

        $this->assertDatabaseHas('tasks', [
            'id'             => $task->id,
            'status'         => 'blocked',
            'blocked_reason' => 'Menunggu approval klien',
        ]);
    }

    // ---------------------------------------------------------------
    // Acceptance criteria: move to non-blocked clears blocked_reason
    // ---------------------------------------------------------------

    #[Test]
    public function move_away_from_blocked_clears_reason(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);
        $task    = $project->tasks()->create([
            'company_id'     => $company->id,
            'title'          => 'Blocked Task',
            'status'         => 'blocked',
            'progress'       => 30,
            'blocked_reason' => 'Old reason',
        ]);

        $this->actingAs($manager)
            ->patch(route('tasks.move', $task), ['status' => 'doing'])
            ->assertRedirect(route('projects.kanban', $project));

        $this->assertDatabaseHas('tasks', [
            'id'             => $task->id,
            'status'         => 'doing',
            'blocked_reason' => null,
        ]);
    }

    // ---------------------------------------------------------------
    // Acceptance criteria: reopen task from done to non-done
    // ---------------------------------------------------------------

    #[Test]
    public function project_manager_can_reopen_task_from_done_to_todo(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);
        $task    = $this->makeTask($project, 'done', 100);

        $this->actingAs($manager)
            ->patch(route('tasks.move', $task), ['status' => 'todo'])
            ->assertRedirect(route('projects.kanban', $project));

        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'status' => 'todo',
        ]);
    }

    // ---------------------------------------------------------------
    // Assignee member can move their task to done
    // ---------------------------------------------------------------

    #[Test]
    public function assignee_member_can_move_task_to_done(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $member  = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);

        $project->members()->attach($member->id);
        $task = $this->makeTask($project, 'doing', 60);
        $task->assignees()->sync([$member->id]);

        $this->actingAs($member)
            ->patch(route('tasks.move', $task), ['status' => 'done'])
            ->assertRedirect(route('projects.kanban', $project));

        $this->assertDatabaseHas('tasks', [
            'id'       => $task->id,
            'status'   => 'done',
            'progress' => 100,
        ]);
    }

    #[Test]
    public function assignee_member_cannot_move_task_to_blocked(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $member  = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);

        $project->members()->attach($member->id);
        $task = $this->makeTask($project, 'doing');
        $task->assignees()->sync([$member->id]);

        // Assignee member can only move to done, not blocked
        $this->actingAs($member)
            ->patch(route('tasks.move', $task), [
                'status'         => 'blocked',
                'blocked_reason' => 'Something blocking',
            ])
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // Invalid status value is rejected
    // ---------------------------------------------------------------

    #[Test]
    public function invalid_status_value_is_rejected(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'employee');
        $project = $this->makeProject($company, $manager);
        $task    = $this->makeTask($project, 'doing');

        $this->actingAs($manager)
            ->patch(route('tasks.move', $task), ['status' => 'invalid_status'])
            ->assertSessionHasErrors('status');
    }
}
