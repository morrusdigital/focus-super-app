<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
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

    private function userAs(string $role, Company $company): User
    {
        return User::factory()->create([
            'role'       => $role,
            'company_id' => $company->id,
        ]);
    }

    // ---------------------------------------------------------------
    // viewAny — index page
    // ---------------------------------------------------------------

    public function test_holding_admin_can_view_user_list(): void
    {
        $holding = $this->createHolding();
        $actor   = $this->userAs('holding_admin', $holding);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertOk();
    }

    public function test_company_admin_can_view_user_list(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertOk();
    }

    public function test_finance_holding_cannot_access_user_list(): void
    {
        $holding = $this->createHolding();
        $actor   = $this->userAs('finance_holding', $holding);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_finance_company_cannot_access_user_list(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('finance_company', $company);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_employee_cannot_access_user_list(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_user_list(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // view — show page
    // ---------------------------------------------------------------

    public function test_holding_admin_can_view_user_from_any_company(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->get(route('users.show', $target));

        $response->assertOk();
    }

    public function test_company_admin_can_view_user_from_same_company(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->get(route('users.show', $target));

        $response->assertOk();
    }

    public function test_company_admin_cannot_view_user_from_different_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $target   = $this->userAs('employee', $companyB);

        $response = $this->actingAs($actor)->get(route('users.show', $target));

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // create / store
    // ---------------------------------------------------------------

    public function test_holding_admin_can_access_create_user_form(): void
    {
        $holding = $this->createHolding();
        $actor   = $this->userAs('holding_admin', $holding);

        $response = $this->actingAs($actor)->get(route('users.create'));

        $response->assertOk();
    }

    public function test_company_admin_can_create_user_for_own_company(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), [
            'name'                  => 'New Employee',
            'email'                 => 'newemployee@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'employee',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email'      => 'newemployee@example.com',
            'company_id' => $company->id,
        ]);
    }

    public function test_holding_admin_can_create_user_for_any_company(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);

        $response = $this->actingAs($actor)->post(route('users.store'), [
            'name'                  => 'Remote Employee',
            'email'                 => 'remote@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'employee',
            'company_id'            => $company->id,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email'      => 'remote@example.com',
            'company_id' => $company->id,
        ]);
    }

    public function test_finance_company_cannot_create_user(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('finance_company', $company);

        $response = $this->actingAs($actor)->get(route('users.create'));

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // update
    // ---------------------------------------------------------------

    public function test_holding_admin_can_edit_user_from_any_company(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->get(route('users.edit', $target));

        $response->assertOk();
    }

    public function test_company_admin_can_edit_user_from_same_company(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->get(route('users.edit', $target));

        $response->assertOk();
    }

    public function test_company_admin_cannot_update_user_from_different_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $target   = $this->userAs('employee', $companyB);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => 'Tampered Name',
            'email' => $target->email,
            'role'  => 'employee',
        ]);

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // activate
    // ---------------------------------------------------------------

    public function test_holding_admin_can_activate_any_user(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = User::factory()->create([
            'role'       => 'employee',
            'company_id' => $company->id,
            'is_active'  => true,
        ]);

        $response = $this->actingAs($actor)->post(route('users.activate', $target));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'        => $target->id,
            'is_active' => false,
        ]);
    }

    public function test_company_admin_can_activate_user_from_same_company(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->post(route('users.activate', $target));

        $response->assertRedirect();
    }

    public function test_company_admin_cannot_activate_user_from_different_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $target   = $this->userAs('employee', $companyB);

        $response = $this->actingAs($actor)->post(route('users.activate', $target));

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // resetPassword
    // ---------------------------------------------------------------

    public function test_holding_admin_can_reset_password_of_any_user(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->post(route('users.reset-password', $target), [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
    }

    public function test_company_admin_cannot_reset_password_of_user_from_different_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $target   = $this->userAs('employee', $companyB);

        $response = $this->actingAs($actor)->post(route('users.reset-password', $target), [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertForbidden();
    }

    public function test_employee_cannot_reset_password_of_another_user(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('employee', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->post(route('users.reset-password', $target), [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertForbidden();
    }
}
