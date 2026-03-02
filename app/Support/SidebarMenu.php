<?php

namespace App\Support;

use App\Models\RoleMenu;
use App\Models\User;

/**
 * Dynamic sidebar menu builder.
 *
 * Menu items are resolved as follows:
 *
 *   holding_admin  → fixed full set (all catalog items + role_management link)
 *   all others     → DB-driven via `role_menus` table (managed by RoleMenuController)
 *
 * Rendering contract (array structure consumed by sidebar.blade.php):
 *   type     : 'link' | 'submenu'
 *   label    : display text
 *   icon     : Feather icon name
 *   route    : named route  (link type)
 *   url      : explicit URL (link type, when route has query params)
 *   active   : routeIs() pattern
 *   active_url          : exact URL for active detection
 *   active_url_exclude  : URL excluded from active detection
 *   children : array of child items (submenu type only)
 *   active_child        : comma-separated routeIs patterns (submenu type)
 */
class SidebarMenu
{
    // ---------------------------------------------------------------
    // Public entry point
    // ---------------------------------------------------------------

    /**
     * Build and return the menu items array for the given user.
     */
    public static function forUser(User $user): array
    {
        if ($user->isHoldingAdmin()) {
            return static::holdingAdminMenu();
        }

        $role = (string) $user->role;
        $keys = RoleMenu::getKeysForRole($role);

        return static::buildFromKeys($keys);
    }

    // ---------------------------------------------------------------
    // Holding admin — fixed, always full access
    // ---------------------------------------------------------------

    private static function holdingAdminMenu(): array
    {
        // All catalog keys + role_management (not in catalog, always injected here)
        $all  = MenuCatalog::all();
        $keys = array_keys($all);

        $items = static::buildFromKeys($keys);

        // Inject "Manajemen Role Menu" right after "Manajemen User" (order 40)
        $roleManagementItem = [
            'type'   => 'link',
            'label'  => 'Manajemen Role Menu',
            'icon'   => 'shield',
            'route'  => 'role-menus.index',
            'active' => 'role-menus.*',
        ];

        // Find where to insert (after user_management link)
        $result   = [];
        $injected = false;
        foreach ($items as $item) {
            $result[] = $item;
            if (! $injected
                && isset($item['route'])
                && $item['route'] === 'users.index'
            ) {
                $result[]  = $roleManagementItem;
                $injected = true;
            }
        }

        if (! $injected) {
            // Fallback: append at end
            $result[] = $roleManagementItem;
        }

        return $result;
    }

    // ---------------------------------------------------------------
    // Build menu items from a list of enabled keys
    // ---------------------------------------------------------------

    /**
     * Convert an array of menu keys into the sidebar items array,
     * auto-grouping 'master' items into a collapsible submenu.
     *
     * @param  string[]  $keys
     */
    private static function buildFromKeys(array $keys): array
    {
        $catalog     = MenuCatalog::all();
        $items       = [];
        $masterItems = [];

        foreach ($catalog as $key => $def) {
            if (! in_array($key, $keys, true)) {
                continue;
            }

            // Master group items are collected separately
            if (($def['group'] ?? null) === 'master') {
                $masterItems[$key] = $def;
                continue;
            }

            $items[] = static::buildLinkItem($key, $def);
        }

        // Append the Master submenu if any master items are enabled
        if (! empty($masterItems)) {
            $items[] = static::buildMasterSubmenu($masterItems);
        }

        return $items;
    }

    // ---------------------------------------------------------------
    // Item builders
    // ---------------------------------------------------------------

    /**
     * Build a single 'link' item from a catalog definition.
     */
    private static function buildLinkItem(string $key, array $def): array
    {
        $item = [
            'type'  => 'link',
            'label' => $def['label'],
            'icon'  => $def['icon'],
        ];

        // URL-based resolution (items with query params, e.g. review_bp)
        if (! empty($def['url_params'])) {
            $item['url']        = route($def['route'], $def['url_params']);
            $item['active_url'] = route($def['route'], $def['url_params']);
        } else {
            $item['route'] = $def['route'];
        }

        // Active pattern
        if (isset($def['active'])) {
            $item['active'] = $def['active'];
        }

        // Active URL exclude logic (all_bp: active on index but NOT with submitted param)
        if (! empty($def['active_url_exclude'])) {
            $item['active_url_exclude'] = route($def['route'], ['status' => 'submitted']);
        }

        return $item;
    }

    /**
     * Build a collapsible 'submenu' item for Master-group items.
     *
     * @param  array<string, array>  $masterItems  key => catalog definition
     */
    private static function buildMasterSubmenu(array $masterItems): array
    {
        $children     = [];
        $activeRoutes = [];

        foreach ($masterItems as $key => $def) {
            $children[]     = [
                'label'  => $def['label'],
                'route'  => $def['route'],
                'active' => $def['active'] ?? null,
            ];
            $activeRoutes[] = $def['active'] ?? $def['route'];
        }

        return [
            'type'         => 'submenu',
            'label'        => 'Master',
            'icon'         => 'folder',
            'active_child' => implode(',', $activeRoutes),
            'children'     => $children,
        ];
    }
}
