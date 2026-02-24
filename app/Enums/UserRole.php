<?php

namespace App\Enums;

enum UserRole: string
{
    case HoldingAdmin   = 'holding_admin';
    case CompanyAdmin   = 'company_admin';
    case ProjectManager = 'project_manager';
    case Member         = 'member';

    /**
     * Mapping legacy role strings to their equivalent new enum value.
     * - 'finance_holding' is treated as HoldingAdmin
     * - 'admin_company'   is treated as CompanyAdmin
     */
    private const LEGACY_MAP = [
        'finance_holding' => self::HoldingAdmin,
        'admin_company'   => self::CompanyAdmin,
    ];

    /**
     * Resolve any role string (legacy or new) into a UserRole enum value.
     * Returns null if the string does not match any known role.
     */
    public static function fromString(string $role): ?self
    {
        // Try to resolve directly from enum value
        $found = self::tryFrom($role);
        if ($found !== null) {
            return $found;
        }

        // Fall back to legacy map
        return self::LEGACY_MAP[$role] ?? null;
    }

    /**
     * Check if a given role string resolves to HoldingAdmin.
     * Accepts both 'holding_admin' and legacy 'finance_holding'.
     */
    public static function isHoldingAdmin(string $role): bool
    {
        return self::fromString($role) === self::HoldingAdmin;
    }

    /**
     * Check if a given role string resolves to CompanyAdmin.
     * Accepts both 'company_admin' and legacy 'admin_company'.
     */
    public static function isCompanyAdmin(string $role): bool
    {
        return self::fromString($role) === self::CompanyAdmin;
    }

    /**
     * Check if a given role string resolves to ProjectManager.
     */
    public static function isProjectManager(string $role): bool
    {
        return self::fromString($role) === self::ProjectManager;
    }

    /**
     * Check if a given role string resolves to Member.
     */
    public static function isMember(string $role): bool
    {
        return self::fromString($role) === self::Member;
    }
}
