<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
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
    // viewAny
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_can_view_any(): void
    {
        $user = $this->makeUser($this->makeCompany(), 'holding_admin');
        $this->assertTrue($user->can('viewAny', Project::class));
    }

    #[Test]
    public function company_admin_can_view_any(): void
    {
        $user = $this->makeUser($this->makeCompany(), 'company_admin');
        $this->assertTrue($user->can('viewAny', Project::class));
    }

    #[Test]
    public function project_manager_can_view_any(): void
    {
        $user = $this->makeUser($this->makeCompany(), 'project_manager');
        $this->assertTrue($user->can('viewAny', Project::class));
    }

    #[Test]
    public function member_can_view_any(): void
    {
        $user = $this->makeUser($this->makeCompany(), 'member');
        $this->assertTrue($user->can('viewAny', Project::class));
    }

    // ---------------------------------------------------------------
    // view — 4 roles
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_can_view_any_project(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'holding_admin');
        $project = $this->makeProject($other);

        $this->assertTrue($user->can('view', $project));
    }

    #[Test]
    public function company_admin_can_view_own_company_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($company);

        $this->assertTrue($user->can('view', $project));
    }

    #[Test]
    public function company_admin_cannot_view_other_company_project(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($other);

        $this->assertFalse($user->can('view', $project));
    }

    #[Test]
    public function project_manager_can_view_project_they_manage(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $user);

        $this->assertTrue($user->can('view', $project));
    }

    #[Test]
    public function project_manager_can_view_project_they_are_member_of(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company);
        $project->members()->attach($user->id);

        $this->assertTrue($user->can('view', $project));
    }

    #[Test]
    public function project_manager_cannot_view_unrelated_project(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($other);

        $this->assertFalse($user->can('view', $project));
    }

    #[Test]
    public function member_can_view_project_they_have_joined(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $project->members()->attach($user->id);

        $this->assertTrue($user->can('view', $project));
    }

    #[Test]
    public function member_cannot_view_project_they_have_not_joined(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);

        $this->assertFalse($user->can('view', $project));
    }

    // ---------------------------------------------------------------
    // update — 4 roles
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_can_update_any_project(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'holding_admin');
        $project = $this->makeProject($other);

        $this->assertTrue($user->can('update', $project));
    }

    #[Test]
    public function company_admin_can_update_own_company_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($company);

        $this->assertTrue($user->can('update', $project));
    }

    #[Test]
    public function company_admin_cannot_update_other_company_project(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($other);

        $this->assertFalse($user->can('update', $project));
    }

    #[Test]
    public function project_manager_cannot_update_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $user);

        // PM manages tasks, but cannot edit the project record itself.
        $this->assertFalse($user->can('update', $project));
    }

    #[Test]
    public function project_manager_cannot_update_project_they_do_not_manage(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company);  // no project_manager_id set

        $this->assertFalse($user->can('update', $project));
    }

    #[Test]
    public function member_cannot_update_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $project->members()->attach($user->id);

        $this->assertFalse($user->can('update', $project));
    }

    // ---------------------------------------------------------------
    // manageMembers
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_can_manage_members_of_any_project(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'holding_admin');
        $project = $this->makeProject($other);

        $this->assertTrue($user->can('manageMembers', $project));
    }

    #[Test]
    public function company_admin_can_manage_members_of_own_company_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($company);

        $this->assertTrue($user->can('manageMembers', $project));
    }

    #[Test]
    public function company_admin_cannot_manage_members_of_other_company_project(): void
    {
        $company = $this->makeCompany();
        $other   = $this->makeCompany();
        $user    = $this->makeUser($company, 'company_admin');
        $project = $this->makeProject($other);

        $this->assertFalse($user->can('manageMembers', $project));
    }

    #[Test]
    public function project_manager_can_manage_members_of_own_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company, $user);

        $this->assertTrue($user->can('manageMembers', $project));
    }

    #[Test]
    public function project_manager_cannot_manage_members_of_unmanaged_project(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'project_manager');
        $project = $this->makeProject($company);

        $this->assertFalse($user->can('manageMembers', $project));
    }

    #[Test]
    public function member_cannot_manage_members(): void
    {
        $company = $this->makeCompany();
        $user    = $this->makeUser($company, 'member');
        $project = $this->makeProject($company);
        $project->members()->attach($user->id);

        $this->assertFalse($user->can('manageMembers', $project));
    }
}
