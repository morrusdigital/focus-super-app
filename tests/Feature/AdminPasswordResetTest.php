<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Issue #32 — Implement Admin Password Reset Flow
 *
 * Scope:
 *  - Action reset password di user management.
 *  - FormRequest validasi password baru + confirmation.
 *  - Policy check berdasarkan role + company.
 *  - Password tersimpan dengan hashing standar Laravel.
 *
 * Acceptance Criteria:
 *  - Admin berwenang bisa reset password user dalam scope-nya.
 *  - Upaya reset lintas scope ditolak.
 *  - Password tersimpan dalam bentuk hash.
 *  - Akses tanpa izin return 403.
 */
class AdminPasswordResetTest extends TestCase
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

    private function postReset(User $actor, User $target, array $payload): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($actor)->post(route('users.reset-password', $target), $payload);
    }

    // ---------------------------------------------------------------
    // Authorization — who can reset
    // ---------------------------------------------------------------

    public function test_holding_admin_can_reset_password_of_any_user(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('users.show', $target));
        $response->assertSessionHas('success');
    }

    public function test_company_admin_can_reset_password_of_user_from_same_company(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('users.show', $target));
        $response->assertSessionHas('success');
    }

    // ---------------------------------------------------------------
    // Authorization — who cannot reset (403)
    // ---------------------------------------------------------------

    public function test_company_admin_cannot_reset_password_of_user_from_different_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $target   = $this->userAs('employee', $companyB);

        $response = $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertForbidden();
    }

    public function test_finance_holding_cannot_reset_password(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('finance_holding', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertForbidden();
    }

    public function test_finance_company_cannot_reset_password(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('finance_company', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
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

        $response = $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_reset_password(): void
    {
        $company = $this->createCompany();
        $target  = $this->userAs('employee', $company);

        $response = $this->post(route('users.reset-password', $target), [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Password hashing
    // ---------------------------------------------------------------

    public function test_password_is_stored_as_hash_not_plaintext(): void
    {
        $holding  = $this->createHolding();
        $company  = $this->createCompany();
        $actor    = $this->userAs('holding_admin', $holding);
        $target   = $this->userAs('employee', $company);
        $newPass  = 'newpassword123';

        $this->postReset($actor, $target, [
            'password'              => $newPass,
            'password_confirmation' => $newPass,
        ]);

        $stored = $target->fresh()->password;

        // Must not be stored as plaintext
        $this->assertNotEquals($newPass, $stored);
        // Must pass Hash::check (bcrypt/argon2 standard)
        $this->assertTrue(Hash::check($newPass, $stored));
    }

    public function test_password_hash_changes_after_reset(): void
    {
        $holding      = $this->createHolding();
        $company      = $this->createCompany();
        $actor        = $this->userAs('holding_admin', $holding);
        $target       = $this->userAs('employee', $company, ['password' => bcrypt('oldpassword')]);
        $originalHash = $target->password;

        $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertNotEquals($originalHash, $target->fresh()->password);
    }

    // ---------------------------------------------------------------
    // Validation — FormRequest rules
    // ---------------------------------------------------------------

    public function test_password_is_required(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
            'password'              => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_confirmation_must_match(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_valid_8_character_password_is_accepted(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->postReset($actor, $target, [
            'password'              => 'exactly8',
            'password_confirmation' => 'exactly8',
        ]);

        $response->assertRedirect(route('users.show', $target));
    }

    // ---------------------------------------------------------------
    // Cross-scope: DB state must not change on rejection
    // ---------------------------------------------------------------

    public function test_cross_scope_reset_does_not_change_password(): void
    {
        $companyA    = $this->createCompany('Company A');
        $companyB    = $this->createCompany('Company B');
        $actor       = $this->userAs('company_admin', $companyA);
        $target      = $this->userAs('employee', $companyB, ['password' => bcrypt('originalpass')]);
        $originalHash = $target->password;

        $this->postReset($actor, $target, [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertEquals($originalHash, $target->fresh()->password);
    }
}
