<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

/**
 * Tests for UserRole enum:
 * - fromString() resolves both new and legacy role strings
 * - Static helper methods isHoldingAdmin, isCompanyAdmin, etc.
 */
class UserRoleEnumTest extends TestCase
{
    // ------------------------------------------------------------------
    // fromString: new role values
    // ------------------------------------------------------------------

    public function test_from_string_resolves_holding_admin(): void
    {
        $this->assertSame(UserRole::HoldingAdmin, UserRole::fromString('holding_admin'));
    }

    public function test_from_string_resolves_company_admin(): void
    {
        $this->assertSame(UserRole::CompanyAdmin, UserRole::fromString('company_admin'));
    }

    public function test_from_string_resolves_project_manager(): void
    {
        $this->assertSame(UserRole::ProjectManager, UserRole::fromString('project_manager'));
    }

    public function test_from_string_resolves_member(): void
    {
        $this->assertSame(UserRole::Member, UserRole::fromString('member'));
    }

    // ------------------------------------------------------------------
    // fromString: legacy role values (compatibility)
    // ------------------------------------------------------------------

    public function test_from_string_resolves_legacy_finance_holding_to_holding_admin(): void
    {
        $this->assertSame(UserRole::HoldingAdmin, UserRole::fromString('finance_holding'));
    }

    public function test_from_string_resolves_legacy_admin_company_to_company_admin(): void
    {
        $this->assertSame(UserRole::CompanyAdmin, UserRole::fromString('admin_company'));
    }

    public function test_from_string_returns_null_for_unknown_role(): void
    {
        $this->assertNull(UserRole::fromString('unknown_role'));
    }

    // ------------------------------------------------------------------
    // Static helper: isHoldingAdmin
    // ------------------------------------------------------------------

    public function test_is_holding_admin_true_for_new_role(): void
    {
        $this->assertTrue(UserRole::isHoldingAdmin('holding_admin'));
    }

    public function test_is_holding_admin_true_for_legacy_finance_holding(): void
    {
        $this->assertTrue(UserRole::isHoldingAdmin('finance_holding'));
    }

    public function test_is_holding_admin_false_for_other_roles(): void
    {
        $this->assertFalse(UserRole::isHoldingAdmin('company_admin'));
        $this->assertFalse(UserRole::isHoldingAdmin('admin_company'));
        $this->assertFalse(UserRole::isHoldingAdmin('project_manager'));
        $this->assertFalse(UserRole::isHoldingAdmin('member'));
    }

    // ------------------------------------------------------------------
    // Static helper: isCompanyAdmin
    // ------------------------------------------------------------------

    public function test_is_company_admin_true_for_new_role(): void
    {
        $this->assertTrue(UserRole::isCompanyAdmin('company_admin'));
    }

    public function test_is_company_admin_true_for_legacy_admin_company(): void
    {
        $this->assertTrue(UserRole::isCompanyAdmin('admin_company'));
    }

    public function test_is_company_admin_false_for_other_roles(): void
    {
        $this->assertFalse(UserRole::isCompanyAdmin('holding_admin'));
        $this->assertFalse(UserRole::isCompanyAdmin('finance_holding'));
        $this->assertFalse(UserRole::isCompanyAdmin('project_manager'));
        $this->assertFalse(UserRole::isCompanyAdmin('member'));
    }

    // ------------------------------------------------------------------
    // Static helper: isProjectManager
    // ------------------------------------------------------------------

    public function test_is_project_manager_true_for_project_manager(): void
    {
        $this->assertTrue(UserRole::isProjectManager('project_manager'));
    }

    public function test_is_project_manager_false_for_other_roles(): void
    {
        $this->assertFalse(UserRole::isProjectManager('holding_admin'));
        $this->assertFalse(UserRole::isProjectManager('company_admin'));
        $this->assertFalse(UserRole::isProjectManager('member'));
    }

    // ------------------------------------------------------------------
    // Static helper: isMember
    // ------------------------------------------------------------------

    public function test_is_member_true_for_member(): void
    {
        $this->assertTrue(UserRole::isMember('member'));
    }

    public function test_is_member_false_for_other_roles(): void
    {
        $this->assertFalse(UserRole::isMember('holding_admin'));
        $this->assertFalse(UserRole::isMember('company_admin'));
        $this->assertFalse(UserRole::isMember('project_manager'));
    }

    // ------------------------------------------------------------------
    // Enum values integrity
    // ------------------------------------------------------------------

    public function test_enum_has_exactly_four_cases(): void
    {
        $this->assertCount(4, UserRole::cases());
    }
}
