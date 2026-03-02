<?php

namespace App\Models;

use App\Support\MenuCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Stores the set of allowed menu keys for a given role.
 *
 * One row per configurable role: company_admin, finance_holding,
 * finance_company, employee.
 *
 * holding_admin is never stored here — its menu is always the full set.
 */
class RoleMenu extends Model
{
    protected $fillable = ['role', 'menu_keys'];

    protected $casts = [
        'menu_keys' => 'array',
    ];

    // ---------------------------------------------------------------
    // Cache helpers
    // ---------------------------------------------------------------

    /** Cache TTL in seconds (10 minutes). */
    private const CACHE_TTL = 600;

    private static function cacheKey(string $role): string
    {
        return "role_menu:{$role}";
    }

    /**
     * Flush the in-memory and Cache store for this role when saved/deleted.
     */
    protected static function booted(): void
    {
        $flush = fn (self $m) => Cache::forget(self::cacheKey($m->role));
        static::saved($flush);
        static::deleted($flush);
    }

    // ---------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------

    /**
     * Return the array of menu keys enabled for a role.
     * Falls back to the catalog defaults if no DB record exists.
     *
     * holding_admin is handled by SidebarMenu directly — never call this for that role.
     */
    public static function getKeysForRole(string $role): array
    {
        return Cache::remember(self::cacheKey($role), self::CACHE_TTL, function () use ($role) {
            $record = static::where('role', $role)->first();

            if ($record) {
                return (array) $record->menu_keys;
            }

            // Fallback to hardcoded defaults (before first seed/config)
            return MenuCatalog::ROLE_DEFAULTS[$role] ?? [];
        });
    }

    /**
     * Persist the menu keys for a role (upsert by role).
     * Also flushes the cache.
     */
    public static function setKeysForRole(string $role, array $keys): self
    {
        // Validate: keep only keys that exist in the catalog
        $valid = array_values(array_intersect($keys, MenuCatalog::configurableKeys()));

        $record = static::updateOrCreate(
            ['role' => $role],
            ['menu_keys' => $valid],
        );

        Cache::forget(self::cacheKey($role));

        return $record;
    }
}
