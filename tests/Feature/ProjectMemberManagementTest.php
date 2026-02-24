<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectMemberManagementTest extends TestCase
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

    // ---------------------------------------------------------------
    // store — add member success
    // ---------------------------------------------------------------

    #[Test]
    public function company_admin_can_add_member_to_own_project(): void
    {
        $company = $this->makeCompany();
        $actor   = $this->makeUser($company, 'company_admin');
        $target  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);

        $this->actingAs($actor)
            ->post(route('projects.members.store', $project), ['user_id' => $target->id])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id'    => $target->id,
        ]);
    }

    #[Test]
    public function project_manager_can_add_member_to_own_project(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $target  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company, $manager);

        $this->actingAs($manager)
            ->post(route('projects.members.store', $project), ['user_id' => $target->id])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id'    => $target->id,
        ]);
    }

    #[Test]
    public function holding_admin_can_add_member_from_different_company(): void
    {
        $holdingCompany = $this->makeCompany();
        $projectCompany = $this->makeCompany();

        $holdingAdmin = $this->makeUser($holdingCompany, 'holding_admin');
        $target       = $this->makeUser($projectCompany, 'member');
        $project      = $this->makeProject($projectCompany);

        // Holding admin adds a user from yet another company into the project
        $otherUser = $this->makeUser($holdingCompany, 'member');

        $this->actingAs($holdingAdmin)
            ->post(route('projects.members.store', $project), ['user_id' => $otherUser->id])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id'    => $otherUser->id,
        ]);
    }

    // ---------------------------------------------------------------
    // store — duplicate rejected
    // ---------------------------------------------------------------

    #[Test]
    public function duplicate_member_is_rejected(): void
    {
        $company = $this->makeCompany();
        $actor   = $this->makeUser($company, 'company_admin');
        $target  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);

        // First add succeeds
        $project->members()->attach($target->id);

        // Second add should fail with validation error
        $this->actingAs($actor)
            ->post(route('projects.members.store', $project), ['user_id' => $target->id])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('project_members', 1);
    }

    // ---------------------------------------------------------------
    // store — cross-company rejected for non-holding
    // ---------------------------------------------------------------

    #[Test]
    public function company_admin_cannot_add_user_from_different_company(): void
    {
        $company      = $this->makeCompany();
        $otherCompany = $this->makeCompany();

        $actor       = $this->makeUser($company, 'company_admin');
        $foreignUser = $this->makeUser($otherCompany, 'member');
        $project     = $this->makeProject($company);

        $this->actingAs($actor)
            ->post(route('projects.members.store', $project), ['user_id' => $foreignUser->id])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id'    => $foreignUser->id,
        ]);
    }

    // ---------------------------------------------------------------
    // store — authorization failures (403)
    // ---------------------------------------------------------------

    #[Test]
    public function member_role_cannot_add_member(): void
    {
        $company = $this->makeCompany();
        $actor   = $this->makeUser($company, 'member');
        $target  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);

        $this->actingAs($actor)
            ->post(route('projects.members.store', $project), ['user_id' => $target->id])
            ->assertForbidden();
    }

    #[Test]
    public function project_manager_cannot_add_member_to_unmanaged_project(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $target  = $this->makeUser($company, 'member');

        // Project has a different manager
        $otherManager = $this->makeUser($company, 'project_manager');
        $project      = $this->makeProject($company, $otherManager);

        $this->actingAs($manager)
            ->post(route('projects.members.store', $project), ['user_id' => $target->id])
            ->assertForbidden();
    }

    #[Test]
    public function company_admin_cannot_add_member_to_other_company_project(): void
    {
        $company      = $this->makeCompany();
        $otherCompany = $this->makeCompany();

        $actor   = $this->makeUser($company, 'company_admin');
        $target  = $this->makeUser($otherCompany, 'member');
        $project = $this->makeProject($otherCompany);

        $this->actingAs($actor)
            ->post(route('projects.members.store', $project), ['user_id' => $target->id])
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // destroy — remove member success
    // ---------------------------------------------------------------

    #[Test]
    public function company_admin_can_remove_member_from_own_project(): void
    {
        $company = $this->makeCompany();
        $actor   = $this->makeUser($company, 'company_admin');
        $member  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);

        $project->members()->attach($member->id);

        $this->actingAs($actor)
            ->delete(route('projects.members.destroy', [$project, $member]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id'    => $member->id,
        ]);
    }

    #[Test]
    public function project_manager_can_remove_member_from_own_project(): void
    {
        $company = $this->makeCompany();
        $manager = $this->makeUser($company, 'project_manager');
        $member  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company, $manager);

        $project->members()->attach($member->id);

        $this->actingAs($manager)
            ->delete(route('projects.members.destroy', [$project, $member]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id'    => $member->id,
        ]);
    }

    #[Test]
    public function holding_admin_can_remove_member_from_any_project(): void
    {
        $holdingCompany = $this->makeCompany();
        $projectCompany = $this->makeCompany();

        $holdingAdmin = $this->makeUser($holdingCompany, 'holding_admin');
        $member       = $this->makeUser($projectCompany, 'member');
        $project      = $this->makeProject($projectCompany);

        $project->members()->attach($member->id);

        $this->actingAs($holdingAdmin)
            ->delete(route('projects.members.destroy', [$project, $member]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id'    => $member->id,
        ]);
    }

    // ---------------------------------------------------------------
    // destroy — authorization failures (403)
    // ---------------------------------------------------------------

    #[Test]
    public function member_role_cannot_remove_member(): void
    {
        $company = $this->makeCompany();
        $actor   = $this->makeUser($company, 'member');
        $target  = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);

        $project->members()->attach($target->id);

        $this->actingAs($actor)
            ->delete(route('projects.members.destroy', [$project, $target]))
            ->assertForbidden();
    }

    #[Test]
    public function company_admin_cannot_remove_member_from_other_company_project(): void
    {
        $company      = $this->makeCompany();
        $otherCompany = $this->makeCompany();

        $actor   = $this->makeUser($company, 'company_admin');
        $member  = $this->makeUser($otherCompany, 'member');
        $project = $this->makeProject($otherCompany);

        $project->members()->attach($member->id);

        $this->actingAs($actor)
            ->delete(route('projects.members.destroy', [$project, $member]))
            ->assertForbidden();
    }
}
