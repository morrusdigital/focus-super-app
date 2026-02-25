<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserListFilterTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createHolding(string $name = 'Holding'): Company
    {
        return Company::create(['name' => $name, 'type' => 'holding']);
    }

    private function createCompany(string $name = 'Company A'): Company
    {
        return Company::create(['name' => $name, 'type' => 'company']);
    }

    private function userAs(string $role, Company $company, array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role'       => $role,
            'company_id' => $company->id,
        ], $attrs));
    }

    // ---------------------------------------------------------------
    // Filter: name
    // ---------------------------------------------------------------

    public function test_filter_by_name_returns_matching_users(): void
    {
        $holding = $this->createHolding();
        $actor   = $this->userAs('holding_admin', $holding);
        $this->userAs('employee', $holding, ['name' => 'Budi Santoso']);
        $this->userAs('employee', $holding, ['name' => 'Siti Rahayu']);

        $response = $this->actingAs($actor)->get(route('users.index', ['name' => 'Budi']));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Rahayu');
    }

    // ---------------------------------------------------------------
    // Filter: email
    // ---------------------------------------------------------------

    public function test_filter_by_email_returns_matching_users(): void
    {
        $holding = $this->createHolding();
        $actor   = $this->userAs('holding_admin', $holding);
        $this->userAs('employee', $holding, ['email' => 'budi@example.com', 'name' => 'Budi']);
        $this->userAs('employee', $holding, ['email' => 'siti@other.com', 'name' => 'Siti']);

        $response = $this->actingAs($actor)->get(route('users.index', ['email' => 'example.com']));

        $response->assertOk();
        $response->assertSee('budi@example.com');
        $response->assertDontSee('siti@other.com');
    }

    // ---------------------------------------------------------------
    // Filter: role
    // ---------------------------------------------------------------

    public function test_filter_by_role_returns_matching_users(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $this->userAs('finance_company', $company, ['name' => 'Finance User']);
        $this->userAs('employee', $company, ['name' => 'Employee User']);

        $response = $this->actingAs($actor)->get(route('users.index', ['role' => 'finance_company']));

        $response->assertOk();
        $response->assertSee('Finance User');
        $response->assertDontSee('Employee User');
    }

    // ---------------------------------------------------------------
    // Filter: is_active
    // ---------------------------------------------------------------

    public function test_filter_by_is_active_1_returns_only_active_users(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $this->userAs('employee', $company, ['name' => 'Active User',   'is_active' => true]);
        $this->userAs('employee', $company, ['name' => 'Inactive User', 'is_active' => false]);

        $response = $this->actingAs($actor)->get(route('users.index', ['is_active' => '1']));

        $response->assertOk();
        $response->assertSee('Active User');
        $response->assertDontSee('Inactive User');
    }

    public function test_filter_by_is_active_0_returns_only_inactive_users(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $this->userAs('employee', $company, ['name' => 'Active User',   'is_active' => true]);
        $this->userAs('employee', $company, ['name' => 'Inactive User', 'is_active' => false]);

        $response = $this->actingAs($actor)->get(route('users.index', ['is_active' => '0']));

        $response->assertOk();
        $response->assertSee('Inactive User');
        $response->assertDontSee('Active User');
    }

    public function test_no_is_active_filter_returns_all_users(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $this->userAs('employee', $company, ['name' => 'Active User',   'is_active' => true]);
        $this->userAs('employee', $company, ['name' => 'Inactive User', 'is_active' => false]);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Active User');
        $response->assertSee('Inactive User');
    }

    // ---------------------------------------------------------------
    // Filter: company (holding_admin only)
    // ---------------------------------------------------------------

    public function test_holding_admin_can_filter_by_company(): void
    {
        $holding  = $this->createHolding();
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('holding_admin', $holding);
        $this->userAs('employee', $companyA, ['name' => 'User A']);
        $this->userAs('employee', $companyB, ['name' => 'User B']);

        $response = $this->actingAs($actor)->get(route('users.index', ['company_id' => $companyA->id]));

        $response->assertOk();
        $response->assertSee('User A');
        $response->assertDontSee('User B');
    }

    // ---------------------------------------------------------------
    // Company scope — no data leakage
    // ---------------------------------------------------------------

    public function test_company_admin_only_sees_own_company_users(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $this->userAs('employee', $companyA, ['name' => 'User A']);
        $this->userAs('employee', $companyB, ['name' => 'User B']);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('User A');
        $response->assertDontSee('User B');
    }

    public function test_company_admin_cannot_bypass_scope_with_company_id_filter(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $this->userAs('employee', $companyB, ['name' => 'User B']);

        // company_admin sends company_id of another company — must not appear
        $response = $this->actingAs($actor)->get(route('users.index', ['company_id' => $companyB->id]));

        $response->assertOk();
        $response->assertDontSee('User B');
    }

    // ---------------------------------------------------------------
    // Pagination
    // ---------------------------------------------------------------

    public function test_pagination_is_present_when_users_exceed_15(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        // Create 16 employees; together with the company_admin actor = 17 total
        User::factory()->count(16)->create([
            'role'       => 'employee',
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertOk();
        // The header renders "(17 total)"
        $response->assertSee('17 total');

        // Page 2 should be accessible and return 200
        $page2 = $this->actingAs($actor)->get(route('users.index', ['page' => 2]));
        $page2->assertOk();
    }

    public function test_pagination_preserves_filter_query_string(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        User::factory()->count(16)->create([
            'role'       => 'employee',
            'company_id' => $company->id,
            'name'       => 'Budi',
        ]);

        $response = $this->actingAs($actor)->get(route('users.index', ['name' => 'Budi', 'page' => 1]));

        $response->assertOk();
        // The next-page link should carry the name filter
        $response->assertSee('name=Budi', false);
    }

    // ---------------------------------------------------------------
    // Combined filters
    // ---------------------------------------------------------------

    public function test_multiple_filters_can_be_combined(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $this->userAs('employee', $company, ['name' => 'Budi', 'is_active' => true]);
        $this->userAs('employee', $company, ['name' => 'Budi Nonaktif', 'is_active' => false]);
        $this->userAs('finance_company', $company, ['name' => 'Budi Finance', 'is_active' => true]);

        $response = $this->actingAs($actor)->get(route('users.index', [
            'name'      => 'Budi',
            'role'      => 'employee',
            'is_active' => '1',
        ]));

        $response->assertOk();
        $response->assertSee('Budi');
        $response->assertDontSee('Budi Nonaktif');
        $response->assertDontSee('Budi Finance');
    }
}
