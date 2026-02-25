<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SidebarMenuVisibilityTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function makeUser(string $role, ?int $companyId = null): User
    {
        $company = Company::factory()->create();
        return User::factory()->create([
            'role'       => $role,
            'company_id' => $companyId ?? $company->id,
        ]);
    }

    /** GET /projects — accessible to all roles, renders sidebar */
    private function sidebarPage(User $user)
    {
        return $this->actingAs($user)->get(route('projects.index'));
    }

    // ---------------------------------------------------------------
    // holding_admin → holding menu
    // ---------------------------------------------------------------

    #[Test]
    public function holding_admin_sees_holding_menu(): void
    {
        $user = $this->makeUser('holding_admin');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertSee('Review BP');
        $response->assertSee('Semua BP');
        $response->assertSee('Rekap Project');
        $response->assertSee('Manajemen User');
        $response->assertDontSee('My Tasks');
        $response->assertDontSee('Overdue Tasks');
    }

    // ---------------------------------------------------------------
    // finance_holding → holding menu (no user management)
    // ---------------------------------------------------------------

    #[Test]
    public function finance_holding_sees_holding_menu(): void
    {
        $user = $this->makeUser('finance_holding');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertSee('Review BP');
        $response->assertSee('Semua BP');
        $response->assertSee('Rekap Project');
        $response->assertDontSee('Manajemen User');
        $response->assertDontSee('My Tasks');
        $response->assertDontSee('Overdue Tasks');
    }

    #[Test]
    public function finance_holding_does_not_see_company_menu(): void
    {
        $user = $this->makeUser('finance_holding');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertDontSee('Ajukan BP');
        $response->assertDontSee('Daftar BP Saya');
        $response->assertDontSee('Rekening');
    }

    // ---------------------------------------------------------------
    // company_admin → company admin menu
    // ---------------------------------------------------------------

    #[Test]
    public function company_admin_sees_company_menu(): void
    {
        $user = $this->makeUser('company_admin');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertSee('Ajukan BP');
        $response->assertSee('Daftar BP Saya');
        $response->assertSee('Rekap Project');
        $response->assertSee('Rekening');
        $response->assertSee('Master');
        $response->assertSee('Manajemen User');
        $response->assertDontSee('My Tasks');
        $response->assertDontSee('Overdue Tasks');
    }

    #[Test]
    public function company_admin_does_not_see_holding_menu(): void
    {
        $user = $this->makeUser('company_admin');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertDontSee('Review BP');
        $response->assertDontSee('Semua BP');
    }

    // ---------------------------------------------------------------
    // finance_company → company admin menu
    // ---------------------------------------------------------------

    #[Test]
    public function finance_company_sees_company_menu(): void
    {
        $user = $this->makeUser('finance_company');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertSee('Ajukan BP');
        $response->assertSee('Rekap Project');
        $response->assertDontSee('Manajemen User');
        $response->assertDontSee('My Tasks');
        $response->assertDontSee('Overdue Tasks');
    }

    // ---------------------------------------------------------------
    // employee → member menu (My Tasks + Overdue Tasks)
    // ---------------------------------------------------------------

    #[Test]
    public function employee_sees_member_menu(): void
    {
        $user = $this->makeUser('employee');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertSee('My Tasks');
        $response->assertSee('Overdue Tasks');
    }

    #[Test]
    public function employee_does_not_see_restricted_menus(): void
    {
        $user = $this->makeUser('employee');

        $response = $this->sidebarPage($user);

        $response->assertOk();
        $response->assertDontSee('Ajukan BP');
        $response->assertDontSee('Daftar BP Saya');
        $response->assertDontSee('Review BP');
        $response->assertDontSee('Rekening');
        $response->assertDontSee('Master');
        $response->assertDontSee('Manajemen User');
    }

    // ---------------------------------------------------------------
    // Backend authorization still blocks direct URL access
    // ---------------------------------------------------------------

    #[Test]
    public function employee_cannot_access_budget_plan_create(): void
    {
        $user = $this->makeUser('employee');

        $this->actingAs($user)
            ->get(route('budget-plans.create'))
            ->assertForbidden();
    }

    #[Test]
    public function guest_cannot_access_projects_and_is_redirected_to_login(): void
    {
        $this->get(route('projects.index'))
            ->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // No render error when role is unknown / empty
    // ---------------------------------------------------------------

    #[Test]
    public function sidebar_renders_without_error_for_all_roles(): void
    {
        foreach (['holding_admin', 'finance_holding', 'company_admin', 'finance_company', 'employee'] as $role) {
            $user = $this->makeUser($role);
            $this->actingAs($user)
                ->get(route('projects.index'))
                ->assertOk();
        }
    }
}
