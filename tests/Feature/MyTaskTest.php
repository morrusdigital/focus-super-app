<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyTaskTest extends TestCase
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

    /** Create a task and optionally assign users. */
    private function makeTask(Project $project, array $attributes = [], array $assignUserIds = []): Task
    {
        $task = $project->tasks()->create(array_merge([
            'company_id' => $project->company_id,
            'title'      => 'Task',
            'status'     => 'todo',
            'progress'   => 0,
        ], $attributes));

        if ($assignUserIds) {
            $task->assignees()->sync($assignUserIds);
        }

        return $task;
    }

    // ---------------------------------------------------------------
    // /tasks/my — only assigned tasks appear
    // ---------------------------------------------------------------

    #[Test]
    public function my_tasks_shows_only_tasks_assigned_to_current_user(): void
    {
        $company  = $this->makeCompany();
        $manager  = $this->makeUser($company, 'project_manager');
        $project  = $this->makeProject($company, $manager);
        $member   = $this->makeUser($company, 'member');

        $project->members()->attach($member->id);

        // Task assigned to member
        $assigned = $this->makeTask($project, ['title' => 'Assigned Task'], [$member->id]);
        // Task NOT assigned to member
        $other    = $this->makeTask($project, ['title' => 'Other Task']);

        $this->actingAs($member)
            ->get(route('tasks.my'))
            ->assertOk()
            ->assertSee('Assigned Task')
            ->assertDontSee('Other Task');
    }

    #[Test]
    public function my_tasks_excludes_done_tasks_by_default(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->makeUser($company, 'member');

        $project->members()->attach($member->id);

        $active = $this->makeTask($project, ['title' => 'Active Task', 'status' => 'doing'], [$member->id]);
        $done   = $this->makeTask($project, ['title' => 'Done Task',   'status' => 'done', 'progress' => 100], [$member->id]);

        $this->actingAs($member)
            ->get(route('tasks.my'))
            ->assertOk()
            ->assertSee('Active Task')
            ->assertDontSee('Done Task');
    }

    #[Test]
    public function my_tasks_sorts_by_due_date_asc_nulls_last(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->makeUser($company, 'member');

        $project->members()->attach($member->id);

        $far   = $this->makeTask($project, ['title' => 'Far Future',  'due_date' => Carbon::today()->addDays(10)], [$member->id]);
        $near  = $this->makeTask($project, ['title' => 'Near Future', 'due_date' => Carbon::today()->addDays(2)],  [$member->id]);
        $null  = $this->makeTask($project, ['title' => 'No Due Date', 'due_date' => null],                         [$member->id]);

        $response = $this->actingAs($member)
            ->get(route('tasks.my'))
            ->assertOk();

        $content = $response->getContent();
        // near < far < null (NULLS LAST)
        $this->assertLessThan(strpos($content, 'Far Future'),  strpos($content, 'Near Future'));
        $this->assertLessThan(strpos($content, 'No Due Date'), strpos($content, 'Far Future'));
    }

    #[Test]
    public function my_tasks_company_admin_sees_only_own_company_tasks(): void
    {
        $companyA = $this->makeCompany();
        $companyB = $this->makeCompany();

        $adminA   = $this->makeUser($companyA, 'company_admin');

        $projectA = $this->makeProject($companyA);
        $projectB = $this->makeProject($companyB);

        $memberA  = $this->makeUser($companyA, 'member');
        $memberB  = $this->makeUser($companyB, 'member');

        $projectA->members()->attach($memberA->id);
        $projectB->members()->attach($memberB->id);

        // Assign adminA to tasks in both companies
        $projectA->members()->attach($adminA->id);
        $taskA = $this->makeTask($projectA, ['title' => 'Task Company A'], [$adminA->id]);

        // No cross-company task — adminA is not a member of projectB, isolation works via company_id
        $taskB = $this->makeTask($projectB, ['title' => 'Task Company B'], [$adminA->id]);

        $this->actingAs($adminA)
            ->get(route('tasks.my'))
            ->assertOk()
            ->assertSee('Task Company A')
            ->assertDontSee('Task Company B');
    }

    // ---------------------------------------------------------------
    // /tasks/overdue — query logic
    // ---------------------------------------------------------------

    #[Test]
    public function overdue_shows_tasks_with_past_due_date_and_not_done(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);

        $overdue  = $this->makeTask($project, ['title' => 'Overdue Task',  'due_date' => Carbon::yesterday(), 'status' => 'doing']);
        $future   = $this->makeTask($project, ['title' => 'Future Task',   'due_date' => Carbon::tomorrow(),  'status' => 'doing']);
        $noDate   = $this->makeTask($project, ['title' => 'No Date Task',  'due_date' => null,                'status' => 'doing']);

        $this->actingAs($manager)
            ->get(route('tasks.overdue'))
            ->assertOk()
            ->assertSee('Overdue Task')
            ->assertDontSee('Future Task')
            ->assertDontSee('No Date Task');
    }

    #[Test]
    public function overdue_excludes_done_tasks(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);

        $overdueDone    = $this->makeTask($project, ['title' => 'Done Overdue',   'due_date' => Carbon::yesterday(), 'status' => 'done',  'progress' => 100]);
        $overdueActive  = $this->makeTask($project, ['title' => 'Active Overdue', 'due_date' => Carbon::yesterday(), 'status' => 'doing']);

        $this->actingAs($manager)
            ->get(route('tasks.overdue'))
            ->assertOk()
            ->assertSee('Active Overdue')
            ->assertDontSee('Done Overdue');
    }

    #[Test]
    public function overdue_member_sees_only_tasks_in_joined_projects(): void
    {
        $company  = $this->makeCompany();
        $manager  = $this->makeUser($company, 'project_manager');

        $projectA = $this->makeProject($company, $manager);
        $projectB = $this->makeProject($company, $manager);

        $member   = $this->makeUser($company, 'member');
        $projectA->members()->attach($member->id);
        // projectB → member NOT joined

        $taskA = $this->makeTask($projectA, ['title' => 'Overdue In Joined',    'due_date' => Carbon::yesterday()]);
        $taskB = $this->makeTask($projectB, ['title' => 'Overdue In Unjoined',  'due_date' => Carbon::yesterday()]);

        $this->actingAs($member)
            ->get(route('tasks.overdue'))
            ->assertOk()
            ->assertSee('Overdue In Joined')
            ->assertDontSee('Overdue In Unjoined');
    }

    #[Test]
    public function overdue_company_isolation_prevents_cross_company_leak(): void
    {
        $companyA = $this->makeCompany();
        $companyB = $this->makeCompany();

        $managerA = $this->makeUser($companyA, 'project_manager');
        $managerB = $this->makeUser($companyB, 'project_manager');

        $projectA = $this->makeProject($companyA, $managerA);
        $projectB = $this->makeProject($companyB, $managerB);

        $overdueA = $this->makeTask($projectA, ['title' => 'Overdue A', 'due_date' => Carbon::yesterday()]);
        $overdueB = $this->makeTask($projectB, ['title' => 'Overdue B', 'due_date' => Carbon::yesterday()]);

        // managerA should see Overdue A but NOT Overdue B
        $this->actingAs($managerA)
            ->get(route('tasks.overdue'))
            ->assertOk()
            ->assertSee('Overdue A')
            ->assertDontSee('Overdue B');
    }

    // ---------------------------------------------------------------
    // Auth guard
    // ---------------------------------------------------------------

    #[Test]
    public function guest_is_redirected_from_my_tasks(): void
    {
        $this->get(route('tasks.my'))->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_is_redirected_from_overdue(): void
    {
        $this->get(route('tasks.overdue'))->assertRedirect(route('login'));
    }
}
