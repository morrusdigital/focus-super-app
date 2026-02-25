<?php

namespace App\Enums;

/**
 * Final global user roles for the Focus Super App.
 *
 * Roles:
 *   holding_admin   – Administrator at holding-company level.
 *   company_admin   – Administrator at subsidiary-company level.
 *   finance_holding – Finance staff at holding level.
 *   finance_company – Finance staff at subsidiary level.
 *   employee        – General employee; project PM / member status is
 *                     determined by the project model (project_manager_id
 *                     and project_members), NOT by this role.
 *
 * Legacy note:
 *   'admin_company' is preserved in LEGACY_MAP for backward compatibility
 *   with old financial-module data. No new code should write this value.
 */
enum UserRole: string
{
    case HoldingAdmin   = 'holding_admin';
    case CompanyAdmin   = 'company_admin';
    case FinanceHolding = 'finance_holding';
    case FinanceCompany = 'finance_company';
    case Employee       = 'employee';

    /**
     * Legacy role strings still accepted as input but NOT stored as new values.
     * Old financial-module data may still contain 'admin_company'.
     */
    private const LEGACY_MAP = [
        'admin_company' => self::CompanyAdmin,
    ];

    /**
     * Resolve any role string (final or legacy) into a UserRole enum value.
     * Returns null if the string does not match any known role.
     */
    public static function fromString(string $role): ?self
    {
        $found = self::tryFrom($role);
        if ($found !== null) {
            return $found;
        }

        return self::LEGACY_MAP[$role] ?? null;
    }

    // ------------------------------------------------------------------
    // Static role-check helpers
    // ------------------------------------------------------------------

    public static function isHoldingAdmin(string $role): bool
    {
        return self::fromString($role) === self::HoldingAdmin;
    }

    public static function isCompanyAdmin(string $role): bool
    {
        return self::fromString($role) === self::CompanyAdmin;
    }

    public static function isFinanceHolding(string $role): bool
    {
        return self::fromString($role) === self::FinanceHolding;
    }

    public static function isFinanceCompany(string $role): bool
    {
        return self::fromString($role) === self::FinanceCompany;
    }

    public static function isEmployee(string $role): bool
    {
        return self::fromString($role) === self::Employee;
    }
}
