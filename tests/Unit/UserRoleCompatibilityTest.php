<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests for User model role helper methods.
 * Covers the 5 final roles + legacy admin_company compat.
 */
class UserRoleCompatibilityTest extends TestCase
{
    private function makeUser(string $role): User
    {
        $user = new User();
        $user->role = $role;

        return $user;
    }

    // ------------------------------------------------------------------
    // isHoldingAdmin — only 'holding_admin'
    // ------------------------------------------------------------------

    public function test_is_holding_admin_true_for_holding_admin_role(): void
    {
        $this->assertTrue($this->makeUser('holding_admin')->isHoldingAdmin());
    }

    public function test_is_holding_admin_false_for_finance_holding(): void
    {
        // finance_holding is now its own role, not an alias for holding_admin
        $this->assertFalse($this->makeUser('finance_holding')->isHoldingAdmin());
    }

    public function test_is_holding_admin_false_for_other_final_roles(): void
    {
        foreach (['company_admin', 'finance_company', 'employee'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isHoldingAdmin(),
                "Expected isHoldingAdmin() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // isCompanyAdmin — 'company_admin' + legacy 'admin_company'
    // ------------------------------------------------------------------

    public function test_is_company_admin_true_for_company_admin_role(): void
    {
        $this->assertTrue($this->makeUser('company_admin')->isCompanyAdmin());
    }

    public function test_is_company_admin_true_for_legacy_admin_company(): void
    {
        $this->assertTrue($this->makeUser('admin_company')->isCompanyAdmin());
    }

    public function test_is_company_admin_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'finance_holding', 'finance_company', 'employee'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isCompanyAdmin(),
                "Expected isCompanyAdmin() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // isFinanceHolding
    // ------------------------------------------------------------------

    public function test_is_finance_holding_true_for_finance_holding_role(): void
    {
        $this->assertTrue($this->makeUser('finance_holding')->isFinanceHolding());
    }

    public function test_is_finance_holding_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'company_admin', 'finance_company', 'employee'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isFinanceHolding(),
                "Expected isFinanceHolding() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // isFinanceCompany
    // ------------------------------------------------------------------

    public function test_is_finance_company_true_for_finance_company_role(): void
    {
        $this->assertTrue($this->makeUser('finance_company')->isFinanceCompany());
    }

    public function test_is_finance_company_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'company_admin', 'finance_holding', 'employee'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isFinanceCompany(),
                "Expected isFinanceCompany() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // isEmployee
    // ------------------------------------------------------------------

    public function test_is_employee_true_for_employee_role(): void
    {
        $this->assertTrue($this->makeUser('employee')->isEmployee());
    }

    public function test_is_employee_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'company_admin', 'finance_holding', 'finance_company'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isEmployee(),
                "Expected isEmployee() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // Cross-role exclusivity — only one helper true per final role
    // ------------------------------------------------------------------

    public function test_only_one_helper_is_true_per_final_role(): void
    {
        $cases = [
            'holding_admin'   => 'isHoldingAdmin',
            'company_admin'   => 'isCompanyAdmin',
            'finance_holding' => 'isFinanceHolding',
            'finance_company' => 'isFinanceCompany',
            'employee'        => 'isEmployee',
        ];

        $helpers = ['isHoldingAdmin', 'isCompanyAdmin', 'isFinanceHolding', 'isFinanceCompany', 'isEmployee'];

        foreach ($cases as $role => $expectedTrueHelper) {
            $user = $this->makeUser($role);
            foreach ($helpers as $helper) {
                if ($helper === $expectedTrueHelper) {
                    $this->assertTrue($user->{$helper}(), "{$helper}() should be true for role [{$role}]");
                } else {
                    $this->assertFalse($user->{$helper}(), "{$helper}() should be false for role [{$role}]");
                }
            }
        }
    }
}
