<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #31 — Add User Activation/Deactivation with Login Guard
 *
 * Scope:
 *  - Inactive user cannot login; gets a clear error message.
 *  - Active user can login normally.
 *  - Admin can toggle is_active; company scope is enforced.
 *  - Status aktif/nonaktif terlihat jelas di list user (badge).
 */
class UserActivationLoginGuardTest extends TestCase
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

    private function userAs(string $role, Company $company, array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'role'       => $role,
            'company_id' => $company->id,
        ], $extra));
    }

    private function postLogin(string $email, string $password): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('login.store'), [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    // ---------------------------------------------------------------
    // Login guard — active user
    // ---------------------------------------------------------------

    public function test_active_user_can_login_successfully(): void
    {
        $company = $this->createCompany();
        $user    = $this->userAs('employee', $company, [
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postLogin($user->email, 'password123');

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    // ---------------------------------------------------------------
    // Login guard — inactive user
    // ---------------------------------------------------------------

    public function test_inactive_user_cannot_login(): void
    {
        $company = $this->createCompany();
        $user    = $this->userAs('employee', $company, [
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        $this->postLogin($user->email, 'password123');

        $this->assertGuest();
    }

    public function test_inactive_user_is_redirected_back_with_error(): void
    {
        $company = $this->createCompany();
        $user    = $this->userAs('employee', $company, [
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->postLogin($user->email, 'password123');

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_user_receives_clear_error_message(): void
    {
        $company = $this->createCompany();
        $user    = $this->userAs('employee', $company, [
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->postLogin($user->email, 'password123');

        $response->assertSessionHasErrors([
            'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
        ]);
    }

    public function test_wrong_password_returns_generic_error_not_inactive_error(): void
    {
        $company = $this->createCompany();
        $user    = $this->userAs('employee', $company, [
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postLogin($user->email, 'wrongpassword');

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah.',
        ]);
    }

    // ---------------------------------------------------------------
    // Activate / Deactivate toggle
    // ---------------------------------------------------------------

    public function test_holding_admin_can_deactivate_active_user(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company, ['is_active' => true]);

        $this->actingAs($actor)->post(route('users.activate', $target));

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_holding_admin_can_reactivate_inactive_user(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company, ['is_active' => false]);

        $this->actingAs($actor)->post(route('users.activate', $target));

        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_activate_redirects_to_show_with_success_message_on_deactivate(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company, ['is_active' => true]);

        $response = $this->actingAs($actor)->post(route('users.activate', $target));

        $response->assertRedirect(route('users.show', $target));
        $response->assertSessionHas('success');
    }

    public function test_activate_redirects_to_show_with_success_message_on_reactivate(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company, ['is_active' => false]);

        $response = $this->actingAs($actor)->post(route('users.activate', $target));

        $response->assertRedirect(route('users.show', $target));
        $response->assertSessionHas('success');
    }

    public function test_company_admin_can_toggle_user_from_same_company(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company, ['is_active' => true]);

        $this->actingAs($actor)->post(route('users.activate', $target));

        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_company_admin_cannot_toggle_user_from_different_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $target   = $this->userAs('employee', $companyB, ['is_active' => true]);

        $response = $this->actingAs($actor)->post(route('users.activate', $target));

        $response->assertForbidden();
        $this->assertTrue($target->fresh()->is_active); // unchanged
    }

    public function test_non_admin_cannot_toggle_user_status(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('employee', $company);
        $target  = $this->userAs('employee', $company, ['is_active' => true]);

        $response = $this->actingAs($actor)->post(route('users.activate', $target));

        $response->assertForbidden();
        $this->assertTrue($target->fresh()->is_active); // unchanged
    }

    // ---------------------------------------------------------------
    // Status display — list
    // ---------------------------------------------------------------

    public function test_active_status_badge_visible_in_user_list(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $this->userAs('employee', $company, ['name' => 'Active Employee', 'is_active' => true]);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertSee('Active Employee');
        $response->assertSee('Aktif');
    }

    public function test_inactive_status_badge_visible_in_user_list(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $this->userAs('employee', $company, ['name' => 'Inactive Employee', 'is_active' => false]);

        $response = $this->actingAs($actor)->get(route('users.index'));

        $response->assertSee('Inactive Employee');
        $response->assertSee('Nonaktif');
    }

    public function test_reactivating_user_allows_login_again(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company, [
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        // Activate the user
        $this->actingAs($actor)->post(route('users.activate', $target));
        $this->assertTrue($target->fresh()->is_active);

        // Now login should succeed
        $this->post(route('login.store'), [
            'email'    => $target->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
    }
}
