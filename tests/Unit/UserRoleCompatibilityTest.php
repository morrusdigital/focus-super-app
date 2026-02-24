<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests for User model role helper methods.
 * Verifies that:
 * - Legacy methods (isFinanceHolding, isAdminCompany) still work.
 * - New methods (isHoldingAdmin, isCompanyAdmin, isProjectManager, isMember)
 *   accept both new role strings and legacy equivalents.
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
    // Legacy methods — must remain backward-compatible
    // ------------------------------------------------------------------

    public function test_is_finance_holding_returns_true_for_finance_holding(): void
    {
        $this->assertTrue($this->makeUser('finance_holding')->isFinanceHolding());
    }

    public function test_is_finance_holding_returns_false_for_holding_admin(): void
    {
        $this->assertFalse($this->makeUser('holding_admin')->isFinanceHolding());
    }

    public function test_is_admin_company_returns_true_for_admin_company(): void
    {
        $this->assertTrue($this->makeUser('admin_company')->isAdminCompany());
    }

    public function test_is_admin_company_returns_false_for_company_admin(): void
    {
        $this->assertFalse($this->makeUser('company_admin')->isAdminCompany());
    }

    // ------------------------------------------------------------------
    // isHoldingAdmin — accepts new AND legacy role
    // ------------------------------------------------------------------

    public function test_is_holding_admin_true_for_holding_admin_role(): void
    {
        $this->assertTrue($this->makeUser('holding_admin')->isHoldingAdmin());
    }

    public function test_is_holding_admin_true_for_legacy_finance_holding(): void
    {
        $this->assertTrue($this->makeUser('finance_holding')->isHoldingAdmin());
    }

    public function test_is_holding_admin_false_for_other_roles(): void
    {
        foreach (['company_admin', 'admin_company', 'project_manager', 'member'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isHoldingAdmin(),
                "Expected isHoldingAdmin() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // isCompanyAdmin — accepts new AND legacy role
    // ------------------------------------------------------------------

    public function test_is_company_admin_true_for_company_admin_role(): void
    {
        $this->assertTrue($this->makeUser('company_admin')->isCompanyAdmin());
    }

    public function test_is_company_admin_true_for_legacy_admin_company(): void
    {
        $this->assertTrue($this->makeUser('admin_company')->isCompanyAdmin());
    }

    public function test_is_company_admin_false_for_other_roles(): void
    {
        foreach (['holding_admin', 'finance_holding', 'project_manager', 'member'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isCompanyAdmin(),
                "Expected isCompanyAdmin() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // isProjectManager
    // ------------------------------------------------------------------

    public function test_is_project_manager_true_for_project_manager_role(): void
    {
        $this->assertTrue($this->makeUser('project_manager')->isProjectManager());
    }

    public function test_is_project_manager_false_for_other_roles(): void
    {
        foreach (['holding_admin', 'finance_holding', 'company_admin', 'admin_company', 'member'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isProjectManager(),
                "Expected isProjectManager() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // isMember
    // ------------------------------------------------------------------

    public function test_is_member_true_for_member_role(): void
    {
        $this->assertTrue($this->makeUser('member')->isMember());
    }

    public function test_is_member_false_for_other_roles(): void
    {
        foreach (['holding_admin', 'finance_holding', 'company_admin', 'admin_company', 'project_manager'] as $role) {
            $this->assertFalse(
                $this->makeUser($role)->isMember(),
                "Expected isMember() to be false for role [{$role}]"
            );
        }
    }

    // ------------------------------------------------------------------
    // Cross-role exclusivity — only one role helper true at a time
    // ------------------------------------------------------------------

    public function test_only_one_helper_is_true_per_role(): void
    {
        $cases = [
            'holding_admin'   => 'isHoldingAdmin',
            'company_admin'   => 'isCompanyAdmin',
            'project_manager' => 'isProjectManager',
            'member'          => 'isMember',
        ];

        $helpers = ['isHoldingAdmin', 'isCompanyAdmin', 'isProjectManager', 'isMember'];

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
