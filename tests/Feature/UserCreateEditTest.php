<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreateEditTest extends TestCase
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

    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'New User',
            'email'                 => 'newuser@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'employee',
        ], $overrides);
    }

    // ---------------------------------------------------------------
    // CREATE — valid cases
    // ---------------------------------------------------------------

    public function test_holding_admin_can_create_user_with_holding_level_role(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'role'       => 'holding_admin',
            'company_id' => $company->id,
        ]));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com', 'role' => 'holding_admin']);
    }

    public function test_holding_admin_can_create_user_with_finance_holding_role(): void
    {
        $holding = $this->createHolding();
        $actor   = $this->userAs('holding_admin', $holding);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'role'       => 'finance_holding',
            'company_id' => $holding->id,
        ]));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com', 'role' => 'finance_holding']);
    }

    public function test_company_admin_can_create_user_with_company_level_roles(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        foreach (['company_admin', 'finance_company', 'employee'] as $role) {
            $email = "{$role}@example.com";

            $response = $this->actingAs($actor)->post(route('users.store'), [
                'name'                  => "User {$role}",
                'email'                 => $email,
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => $role,
            ]);

            $response->assertRedirect(route('users.index'));
            $this->assertDatabaseHas('users', ['email' => $email, 'role' => $role]);
        }
    }

    // ---------------------------------------------------------------
    // CREATE — privilege escalation guard
    // ---------------------------------------------------------------

    public function test_company_admin_cannot_create_user_with_holding_admin_role(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'role' => 'holding_admin',
        ]));

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);
    }

    public function test_company_admin_cannot_create_user_with_finance_holding_role(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'role' => 'finance_holding',
        ]));

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);
    }

    // ---------------------------------------------------------------
    // CREATE — validation rules
    // ---------------------------------------------------------------

    public function test_store_rejects_missing_name(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'name' => '',
        ]));

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_rejects_invalid_email_format(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'email' => 'not-an-email',
        ]));

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        $company  = $this->createCompany();
        $actor    = $this->userAs('company_admin', $company);
        $existing = $this->userAs('employee', $company, ['email' => 'taken@example.com']);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'email' => 'taken@example.com',
        ]));

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_rejects_password_confirmation_mismatch(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'password'              => 'password123',
            'password_confirmation' => 'different999',
        ]));

        $response->assertSessionHasErrors(['password']);
    }

    public function test_store_rejects_password_too_short(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertSessionHasErrors(['password']);
    }

    public function test_store_rejects_invalid_role_string(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);

        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'role' => 'superuser',
        ]));

        $response->assertSessionHasErrors(['role']);
    }

    public function test_store_rejects_company_id_sent_by_company_admin(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);

        // company_admin sending company_id is prohibited
        $response = $this->actingAs($actor)->post(route('users.store'), $this->validStorePayload([
            'company_id' => $companyB->id,
        ]));

        $response->assertSessionHasErrors(['company_id']);
    }

    // ---------------------------------------------------------------
    // EDIT — valid cases
    // ---------------------------------------------------------------

    public function test_holding_admin_can_update_user_to_any_role(): void
    {
        $holding = $this->createHolding();
        $company = $this->createCompany();
        $actor   = $this->userAs('holding_admin', $holding);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => $target->name,
            'email' => $target->email,
            'role'  => 'holding_admin',
        ]);

        $response->assertRedirect(route('users.show', $target));
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'holding_admin']);
    }

    public function test_company_admin_can_update_user_within_own_company(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => 'Updated Name',
            'email' => $target->email,
            'role'  => 'finance_company',
        ]);

        $response->assertRedirect(route('users.show', $target));
        $this->assertDatabaseHas('users', [
            'id'   => $target->id,
            'name' => 'Updated Name',
            'role' => 'finance_company',
        ]);
    }

    // ---------------------------------------------------------------
    // EDIT — privilege escalation guard
    // ---------------------------------------------------------------

    public function test_company_admin_cannot_update_user_role_to_holding_admin(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => $target->name,
            'email' => $target->email,
            'role'  => 'holding_admin',
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'employee']);
    }

    public function test_company_admin_cannot_update_user_role_to_finance_holding(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => $target->name,
            'email' => $target->email,
            'role'  => 'finance_holding',
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'employee']);
    }

    // ---------------------------------------------------------------
    // EDIT — no cross-company move
    // ---------------------------------------------------------------

    public function test_company_admin_cannot_move_user_to_another_company_via_update(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $actor    = $this->userAs('company_admin', $companyA);
        $target   = $this->userAs('employee', $companyA);

        // Craft a PUT request that also sends company_id of another company.
        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'       => $target->name,
            'email'      => $target->email,
            'role'       => 'employee',
            'company_id' => $companyB->id,
        ]);

        // The 'prohibited' rule returns 302 with session errors (web route).
        $response->assertSessionHasErrors(['company_id']);
        // Database must be unchanged.
        $this->assertDatabaseHas('users', ['id' => $target->id, 'company_id' => $companyA->id]);
    }

    // ---------------------------------------------------------------
    // EDIT — validation rules
    // ---------------------------------------------------------------

    public function test_update_rejects_missing_name(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => '',
            'email' => $target->email,
            'role'  => 'employee',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_rejects_duplicate_email(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company);
        $other   = $this->userAs('employee', $company, ['email' => 'taken@example.com']);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => $target->name,
            'email' => 'taken@example.com',
            'role'  => 'employee',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_accepts_users_own_email_unchanged(): void
    {
        $company = $this->createCompany();
        $actor   = $this->userAs('company_admin', $company);
        $target  = $this->userAs('employee', $company, ['email' => 'mine@example.com']);

        $response = $this->actingAs($actor)->put(route('users.update', $target), [
            'name'  => 'Updated Name',
            'email' => 'mine@example.com',   // same as own — must be accepted
            'role'  => 'employee',
        ]);

        $response->assertRedirect(route('users.show', $target));
    }
}
