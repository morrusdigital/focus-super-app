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

class TaskCrudTest extends TestCase
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

    /** Attach a user as project member and return the user. */
    private function addMember(Project $project, User $user): User
    {
        $project->members()->attach($user->id);

        return $user;
    }

    private function validPayload(User $member, array $overrides = []): array
    {
        return array_merge([
            'title'     => 'Test Task',
            'status'    => 'todo',
            'progress'  => 0,
            'due_date'  => null,
            'assignees' => [$member->id],
        ], $overrides);
    }

    // ---------------------------------------------------------------
    // CREATE — valid
    // ---------------------------------------------------------------

    #[Test]
    public function project_manager_can_create_task(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->addMember($project, $this->makeUser($company, 'member'));

        $this->actingAs($manager)
            ->post(route('projects.tasks.store', $project), $this->validPayload($member))
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title'      => 'Test Task',
            'status'     => 'todo',
            'progress'   => 0,
        ]);
    }

    #[Test]
    public function company_admin_can_create_task(): void
    {
        $company = $this->makeCompany();
        $admin   = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($company);
        $member  = $this->addMember($project, $this->makeUser($company, 'member'));

        $this->actingAs($admin)
            ->post(route('projects.tasks.store', $project), $this->validPayload($member))
            ->assertRedirect(route('projects.tasks.index', $project));

        $task = Task::where('project_id', $project->id)->firstOrFail();
        $this->assertDatabaseHas('task_assignees', [
            'task_id' => $task->id,
            'user_id' => $member->id,
        ]);
    }

    #[Test]
    public function member_role_cannot_create_task(): void
    {
        $company = $this->makeCompany();
        $actor   = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $member  = $this->addMember($project, $actor);

        $this->actingAs($actor)
            ->post(route('projects.tasks.store', $project), $this->validPayload($member))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // CREATE — multi-assignee saved correctly
    // ---------------------------------------------------------------

    #[Test]
    public function multi_assignee_is_saved_correctly(): void
    {
        $company  = $this->makeCompany();
        $manager  = $this->makeUser($company, 'project_manager');
        $project  = $this->makeProject($company, $manager);
        $member1  = $this->addMember($project, $this->makeUser($company, 'member'));
        $member2  = $this->addMember($project, $this->makeUser($company, 'member'));

        $this->actingAs($manager)
            ->post(route('projects.tasks.store', $project), [
                'title'     => 'Multi Assignee Task',
                'status'    => 'doing',
                'progress'  => 30,
                'assignees' => [$member1->id, $member2->id],
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $task = Task::where('project_id', $project->id)->firstOrFail();

        $this->assertDatabaseHas('task_assignees', ['task_id' => $task->id, 'user_id' => $member1->id]);
        $this->assertDatabaseHas('task_assignees', ['task_id' => $task->id, 'user_id' => $member2->id]);
    }

    // ---------------------------------------------------------------
    // Business rule: blocked without reason rejected
    // ---------------------------------------------------------------

    #[Test]
    public function blocked_status_without_reason_is_rejected(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->addMember($project, $this->makeUser($company, 'member'));

        $this->actingAs($manager)
            ->post(route('projects.tasks.store', $project), [
                'title'          => 'Blocked Task',
                'status'         => 'blocked',
                'progress'       => 50,
                'blocked_reason' => '',
                'assignees'      => [$member->id],
            ])
            ->assertSessionHasErrors('blocked_reason');
    }

    #[Test]
    public function blocked_status_with_reason_is_accepted(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->addMember($project, $this->makeUser($company, 'member'));

        $this->actingAs($manager)
            ->post(route('projects.tasks.store', $project), [
                'title'          => 'Blocked Task',
                'status'         => 'blocked',
                'progress'       => 50,
                'blocked_reason' => 'Waiting for client approval',
                'assignees'      => [$member->id],
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', [
            'project_id'     => $project->id,
            'status'         => 'blocked',
            'blocked_reason' => 'Waiting for client approval',
        ]);
    }

    // ---------------------------------------------------------------
    // Business rule: progress 100 => status done
    // ---------------------------------------------------------------

    #[Test]
    public function progress_100_auto_sets_status_done(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->addMember($project, $this->makeUser($company, 'member'));

        $this->actingAs($manager)
            ->post(route('projects.tasks.store', $project), [
                'title'     => 'Almost Done Task',
                'status'    => 'doing',   // input says doing
                'progress'  => 100,       // but progress is 100 → should become done
                'assignees' => [$member->id],
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'status'     => 'done',
            'progress'   => 100,
        ]);
    }

    // ---------------------------------------------------------------
    // Business rule: status done => progress 100
    // ---------------------------------------------------------------

    #[Test]
    public function status_done_auto_sets_progress_100(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->addMember($project, $this->makeUser($company, 'member'));

        $this->actingAs($manager)
            ->post(route('projects.tasks.store', $project), [
                'title'     => 'Done Task',
                'status'    => 'done',   // status done
                'progress'  => 50,       // but progress is 50 → should become 100
                'assignees' => [$member->id],
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'status'     => 'done',
            'progress'   => 100,
        ]);
    }

    // ---------------------------------------------------------------
    // UPDATE — valid
    // ---------------------------------------------------------------

    #[Test]
    public function project_manager_can_update_task(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);
        $member  = $this->addMember($project, $this->makeUser($company, 'member'));

        $task = $project->tasks()->create([
            'company_id' => $company->id,
            'title'      => 'Old Title',
            'status'     => 'todo',
            'progress'   => 0,
        ]);
        $task->assignees()->sync([$member->id]);

        $this->actingAs($manager)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title'     => 'Updated Title',
                'status'    => 'doing',
                'progress'  => 40,
                'assignees' => [$member->id],
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', [
            'id'       => $task->id,
            'title'    => 'Updated Title',
            'status'   => 'doing',
            'progress' => 40,
        ]);
    }

    #[Test]
    public function update_syncs_assignees_correctly(): void
    {
        $company  = $this->makeCompany();
        $manager  = $this->makeUser($company, 'project_manager');
        $project  = $this->makeProject($company, $manager);
        $member1  = $this->addMember($project, $this->makeUser($company, 'member'));
        $member2  = $this->addMember($project, $this->makeUser($company, 'member'));

        $task = $project->tasks()->create([
            'company_id' => $company->id,
            'title'      => 'Task',
            'status'     => 'todo',
            'progress'   => 0,
        ]);
        $task->assignees()->sync([$member1->id]);

        // Change to member2 only
        $this->actingAs($manager)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title'     => 'Task',
                'status'    => 'doing',
                'progress'  => 20,
                'assignees' => [$member2->id],
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseMissing('task_assignees', ['task_id' => $task->id, 'user_id' => $member1->id]);
        $this->assertDatabaseHas('task_assignees',    ['task_id' => $task->id, 'user_id' => $member2->id]);
    }

    // ---------------------------------------------------------------
    // patchStatus
    // ---------------------------------------------------------------

    #[Test]
    public function patch_status_blocked_without_reason_is_rejected(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);

        $task = $project->tasks()->create([
            'company_id' => $company->id,
            'title'      => 'Task',
            'status'     => 'doing',
            'progress'   => 30,
        ]);

        $this->actingAs($manager)
            ->patch(route('projects.tasks.status', [$project, $task]), [
                'status'         => 'blocked',
                'progress'       => 30,
                'blocked_reason' => '',
            ])
            ->assertSessionHasErrors('blocked_reason');
    }

    #[Test]
    public function patch_status_sets_progress_100_when_done(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $manager);

        $task = $project->tasks()->create([
            'company_id' => $company->id,
            'title'      => 'Task',
            'status'     => 'doing',
            'progress'   => 60,
        ]);

        $this->actingAs($manager)
            ->patch(route('projects.tasks.status', [$project, $task]), [
                'status'   => 'done',
                'progress' => 60,
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', [
            'id'       => $task->id,
            'status'   => 'done',
            'progress' => 100,
        ]);
    }

    #[Test]
    public function assignee_member_can_patch_status_to_done(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $member  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company, $manager);

        $project->members()->attach($member->id);

        $task = $project->tasks()->create([
            'company_id' => $company->id,
            'title'      => 'Task',
            'status'     => 'doing',
            'progress'   => 60,
        ]);
        $task->assignees()->sync([$member->id]);

        $this->actingAs($member)
            ->patch(route('projects.tasks.status', [$project, $task]), [
                'status'   => 'done',
                'progress' => 100,
            ])
            ->assertRedirect(route('projects.tasks.index', $project));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done', 'progress' => 100]);
    }
}
