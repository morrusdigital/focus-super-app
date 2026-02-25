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

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function makeCompany(): Company
    {
        return Company::factory()->create();
    }

    private function makeProject(Company $company, ?User $manager = null): Project
    {
        return Project::factory()->create([
            'company_id'         => $company->id,
            'project_manager_id' => $manager?->id,
        ]);
    }

    private function makeUser(Company $company, string $role): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'role'       => $role,
        ]);
    }

    private function makeTask(Company $company, Project $project): Task
    {
        return Task::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'title'      => 'Test Task',
            'status'     => TaskStatus::Todo,
            'progress'   => 0,
            'priority'   => 'medium',
        ]);
    }

    // ---------------------------------------------------------------
    // view
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_cannot_view_task(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'holding_admin');
        $project = $this->makeProject($other);
        $task    = $this->makeTask($other, $project);

        // holding_admin manages projects only, not tasks
        $this->assertFalse($user->can('view', $task));
    }

    #[Test]
    public function company_admin_cannot_view_task_in_own_company(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        // company_admin manages projects only, not tasks
        $this->assertFalse($user->can('view', $task));
    }

    #[Test]
    public function company_admin_cannot_view_task_in_other_company(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($other);
        $task    = $this->makeTask($other, $project);

        $this->assertFalse($user->can('view', $task));
    }

    #[Test]
    public function project_manager_can_view_task_in_own_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $user);
        $task    = $this->makeTask($company, $project);

        $this->assertTrue($user->can('view', $task));
    }

    #[Test]
    public function project_manager_cannot_view_task_in_unmanaged_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company); // no manager
        $task    = $this->makeTask($company, $project);

        $this->assertFalse($user->can('view', $task));
    }

    #[Test]
    public function member_can_view_task_in_joined_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);
        $project->members()->attach($user->id);

        $this->assertTrue($user->can('view', $task));
    }

    #[Test]
    public function member_cannot_view_task_in_unjoined_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $this->assertFalse($user->can('view', $task));
    }

    // ---------------------------------------------------------------
    // update
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_cannot_update_task(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'holding_admin');
        $project = $this->makeProject($other);
        $task    = $this->makeTask($other, $project);

        $this->assertFalse($user->can('update', $task));
    }

    #[Test]
    public function company_admin_cannot_update_task_in_own_company(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $this->assertFalse($user->can('update', $task));
    }

    #[Test]
    public function company_admin_cannot_update_task_in_other_company(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($other);
        $task    = $this->makeTask($other, $project);

        $this->assertFalse($user->can('update', $task));
    }

    #[Test]
    public function project_manager_can_update_task_in_own_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $user);
        $task    = $this->makeTask($company, $project);

        $this->assertTrue($user->can('update', $task));
    }

    #[Test]
    public function project_manager_cannot_update_task_in_unmanaged_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $this->assertFalse($user->can('update', $task));
    }

    #[Test]
    public function member_cannot_update_task(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);
        $project->members()->attach($user->id);

        $this->assertFalse($user->can('update', $task));
    }

    // ---------------------------------------------------------------
    // markDone
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_cannot_mark_done_task(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'holding_admin');
        $project = $this->makeProject($other);
        $task    = $this->makeTask($other, $project);

        $this->assertFalse($user->can('markDone', $task));
    }

    #[Test]
    public function company_admin_cannot_mark_done_task_in_own_company(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $this->assertFalse($user->can('markDone', $task));
    }

    #[Test]
    public function company_admin_cannot_mark_done_task_in_other_company(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($other);
        $task    = $this->makeTask($other, $project);

        $this->assertFalse($user->can('markDone', $task));
    }

    #[Test]
    public function project_manager_can_mark_done_task_in_own_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $user);
        $task    = $this->makeTask($company, $project);

        $this->assertTrue($user->can('markDone', $task));
    }

    #[Test]
    public function member_who_is_assignee_can_mark_done(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $project->members()->attach($user->id);
        $task->assignees()->attach($user->id);

        $this->assertTrue($user->can('markDone', $task));
    }

    #[Test]
    public function member_who_is_not_assignee_cannot_mark_done(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        $project->members()->attach($user->id);
        // NOT attached as assignee

        $this->assertFalse($user->can('markDone', $task));
    }

    #[Test]
    public function member_who_is_not_project_member_cannot_mark_done(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $task    = $this->makeTask($company, $project);

        // Not a project member, not an assignee

        $this->assertFalse($user->can('markDone', $task));
    }
}
