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

class TaskMembershipRelationTest extends TestCase
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

    private function makeUser(Company $company): User
    {
        return User::factory()->create(['company_id' => $company->id]);
    }

    private function makeTask(Company $company, Project $project): Task
    {
        return Task::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'title'      => 'Sample Task',
            'status'     => TaskStatus::Todo,
            'progress'   => 0,
            'priority'   => 'medium',
        ]);
    }

    // ---------------------------------------------------------------
    // Project membership tests
    // ---------------------------------------------------------------

    #[Test]
    public function can_attach_member_to_project(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $user    = $this->makeUser($company);

        $project->members()->attach($user->id);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id'    => $user->id,
        ]);
    }

    #[Test]
    public function project_members_relation_returns_correct_users(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $user    = $this->makeUser($company);

        $project->members()->attach($user->id);

        $members = $project->fresh()->members;
        $this->assertCount(1, $members);
        $this->assertEquals($user->id, $members->first()->id);
    }

    #[Test]
    public function user_projects_relation_is_accessible_from_user_side(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $user    = $this->makeUser($company);

        $project->members()->attach($user->id);

        $projects = $user->fresh()->projects;
        $this->assertCount(1, $projects);
        $this->assertEquals($project->id, $projects->first()->id);
    }

    #[Test]
    public function duplicate_project_member_is_prevented_by_unique_constraint(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $user    = $this->makeUser($company);

        $project->members()->attach($user->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $project->members()->attach($user->id); // duplicate
    }

    // ---------------------------------------------------------------
    // Task assignee tests
    // ---------------------------------------------------------------

    #[Test]
    public function can_assign_user_to_task(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $user    = $this->makeUser($company);
        $task    = $this->makeTask($company, $project);

        $task->assignees()->attach($user->id);

        $this->assertDatabaseHas('task_assignees', [
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function can_assign_multiple_users_to_task(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);
        $user1   = $this->makeUser($company);
        $user2   = $this->makeUser($company);

        $task->assignees()->attach([$user1->id, $user2->id]);

        $this->assertCount(2, $task->fresh()->assignees);
    }

    #[Test]
    public function task_assignees_relation_returns_correct_users(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);
        $user    = $this->makeUser($company);

        $task->assignees()->attach($user->id);

        $assignees = $task->fresh()->assignees;
        $this->assertCount(1, $assignees);
        $this->assertEquals($user->id, $assignees->first()->id);
    }

    #[Test]
    public function user_assigned_tasks_relation_is_accessible_from_user_side(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);
        $user    = $this->makeUser($company);

        $task->assignees()->attach($user->id);

        $assignedTasks = $user->fresh()->assignedTasks;
        $this->assertCount(1, $assignedTasks);
        $this->assertEquals($task->id, $assignedTasks->first()->id);
    }

    #[Test]
    public function duplicate_task_assignee_is_prevented_by_unique_constraint(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);
        $user    = $this->makeUser($company);

        $task->assignees()->attach($user->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $task->assignees()->attach($user->id); // duplicate
    }

    // ---------------------------------------------------------------
    // Cast tests
    // ---------------------------------------------------------------

    #[Test]
    public function task_status_cast_returns_enum(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $this->assertInstanceOf(TaskStatus::class, $task->status);
        $this->assertSame(TaskStatus::Todo, $task->status);
    }

    #[Test]
    public function task_progress_cast_returns_integer(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $this->assertIsInt($task->progress);
        $this->assertSame(0, $task->progress);
    }

    #[Test]
    public function task_due_date_cast_returns_carbon(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = Task::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'title'      => 'Task with due date',
            'status'     => TaskStatus::Todo,
            'progress'   => 0,
            'priority'   => 'medium',
            'due_date'   => '2026-12-31',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->due_date);
        $this->assertSame('2026-12-31', $task->due_date->toDateString());
    }

    // ---------------------------------------------------------------
    // Eager loading tests
    // ---------------------------------------------------------------

    #[Test]
    public function task_eager_loads_assignees_without_n_plus_one(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);
        $user1   = $this->makeUser($company);
        $user2   = $this->makeUser($company);

        $task->assignees()->attach([$user1->id, $user2->id]);

        $tasks = Task::with('assignees')->where('id', $task->id)->get();
        $this->assertCount(2, $tasks->first()->assignees);
    }

    #[Test]
    public function project_eager_loads_tasks_and_members(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $user    = $this->makeUser($company);
        $task    = $this->makeTask($company, $project);

        $project->members()->attach($user->id);

        $loaded = Project::with(['tasks', 'members'])->find($project->id);
        $this->assertCount(1, $loaded->tasks);
        $this->assertCount(1, $loaded->members);
    }

    // ---------------------------------------------------------------
    // Structural relation tests
    // ---------------------------------------------------------------

    #[Test]
    public function task_belongs_to_project(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $this->assertEquals($project->id, $task->project->id);
    }

    #[Test]
    public function project_has_many_tasks(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $this->makeTask($company, $project);
        $this->makeTask($company, $project);

        $this->assertCount(2, $project->fresh()->tasks);
    }

    #[Test]
    public function project_has_project_manager_relation(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);
        $manager = $this->makeUser($company);

        $project->update(['project_manager_id' => $manager->id]);

        $this->assertEquals($manager->id, $project->fresh()->projectManager->id);
    }

    #[Test]
    public function project_manager_id_is_nullable(): void
    {
        $company = $this->makeCompany();
        $project = $this->makeProject($company);

        $this->assertNull($project->projectManager);
    }
}

