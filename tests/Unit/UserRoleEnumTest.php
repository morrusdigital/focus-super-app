<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the final UserRole enum (5 roles).
 */
class UserRoleEnumTest extends TestCase
{
    // ------------------------------------------------------------------
    // fromString: final role values
    // ------------------------------------------------------------------

    public function test_from_string_resolves_holding_admin(): void
    {
        $this->assertSame(UserRole::HoldingAdmin, UserRole::fromString('holding_admin'));
    }

    public function test_from_string_resolves_company_admin(): void
    {
        $this->assertSame(UserRole::CompanyAdmin, UserRole::fromString('company_admin'));
    }

    public function test_from_string_resolves_finance_holding(): void
    {
        $this->assertSame(UserRole::FinanceHolding, UserRole::fromString('finance_holding'));
    }

    public function test_from_string_resolves_finance_company(): void
    {
        $this->assertSame(UserRole::FinanceCompany, UserRole::fromString('finance_company'));
    }

    public function test_from_string_resolves_employee(): void
    {
        $this->assertSame(UserRole::Employee, UserRole::fromString('employee'));
    }

    // ------------------------------------------------------------------
    // fromString: legacy compatibility (admin_company only)
    // ------------------------------------------------------------------

    public function test_from_string_resolves_legacy_admin_company_to_company_admin(): void
    {
        $this->assertSame(UserRole::CompanyAdmin, UserRole::fromString('admin_company'));
    }

    public function test_from_string_returns_null_for_removed_role_project_manager(): void
    {
        $this->assertNull(UserRole::fromString('project_manager'));
    }

    public function test_from_string_returns_null_for_removed_role_member(): void
    {
        $this->assertNull(UserRole::fromString('member'));
    }

    public function test_from_string_returns_null_for_unknown_role(): void
    {
        $this->assertNull(UserRole::fromString('unknown_role'));
    }

    // ------------------------------------------------------------------
    // Static helpers
    // ------------------------------------------------------------------

    public function test_is_holding_admin_true_for_holding_admin(): void
    {
        $this->assertTrue(UserRole::isHoldingAdmin('holding_admin'));
    }

    public function test_is_holding_admin_false_for_other_final_roles(): void
    {
        foreach (['company_admin', 'finance_holding', 'finance_company', 'employee'] as $role) {
            $this->assertFalse(UserRole::isHoldingAdmin($role), "Expected false for [{$role}]");
        }
    }

    public function test_is_company_admin_true_for_company_admin(): void
    {
        $this->assertTrue(UserRole::isCompanyAdmin('company_admin'));
    }

    public function test_is_company_admin_true_for_legacy_admin_company(): void
    {
        $this->assertTrue(UserRole::isCompanyAdmin('admin_company'));
    }

    public function test_is_company_admin_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'finance_holding', 'finance_company', 'employee'] as $role) {
            $this->assertFalse(UserRole::isCompanyAdmin($role), "Expected false for [{$role}]");
        }
    }

    public function test_is_finance_holding_true_for_finance_holding(): void
    {
        $this->assertTrue(UserRole::isFinanceHolding('finance_holding'));
    }

    public function test_is_finance_holding_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'company_admin', 'finance_company', 'employee'] as $role) {
            $this->assertFalse(UserRole::isFinanceHolding($role), "Expected false for [{$role}]");
        }
    }

    public function test_is_finance_company_true_for_finance_company(): void
    {
        $this->assertTrue(UserRole::isFinanceCompany('finance_company'));
    }

    public function test_is_finance_company_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'company_admin', 'finance_holding', 'employee'] as $role) {
            $this->assertFalse(UserRole::isFinanceCompany($role), "Expected false for [{$role}]");
        }
    }

    public function test_is_employee_true_for_employee(): void
    {
        $this->assertTrue(UserRole::isEmployee('employee'));
    }

    public function test_is_employee_false_for_other_final_roles(): void
    {
        foreach (['holding_admin', 'company_admin', 'finance_holding', 'finance_company'] as $role) {
            $this->assertFalse(UserRole::isEmployee($role), "Expected false for [{$role}]");
        }
    }

    // ------------------------------------------------------------------
    // Enum has exactly 5 final cases
    // ------------------------------------------------------------------

    public function test_enum_has_exactly_five_cases(): void
    {
        $this->assertCount(5, UserRole::cases());
    }
}
