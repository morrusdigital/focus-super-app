<?php

namespace App\Http\Controllers;

use App\Models\RoleMenu;
use App\Support\MenuCatalog;
use Illuminate\Http\Request;

/**
 * RoleMenuController — only accessible by holding_admin.
 *
 * index  : overview of all configurable roles + their current menu keys
 * edit   : form to select which menu items are enabled for a specific role
 * update : persist the selection
 */
class RoleMenuController extends Controller
{
    // ---------------------------------------------------------------
    // Configurable roles (holding_admin is excluded — its menu is fixed)
    // ---------------------------------------------------------------
    private const CONFIGURABLE_ROLES = [
        'company_admin',
        'finance_holding',
        'finance_company',
        'employee',
    ];

    // ---------------------------------------------------------------
    // index — overview of all roles
    // ---------------------------------------------------------------

    public function index()
    {
        $this->authorize('viewAny', RoleMenu::class);

        $catalog    = MenuCatalog::all();
        $roleLabels = MenuCatalog::roleLabels();

        $roleData = [];
        foreach (self::CONFIGURABLE_ROLES as $role) {
            $keys = RoleMenu::getKeysForRole($role);
            $roleData[$role] = [
                'label'       => $roleLabels[$role] ?? $role,
                'menu_keys'   => $keys,
                'menu_count'  => count($keys),
                'total_items' => count($catalog),
            ];
        }

        return view('role-menus.index', compact('roleData', 'catalog', 'roleLabels'));
    }

    // ---------------------------------------------------------------
    // edit — form for a specific role
    // ---------------------------------------------------------------

    public function edit(string $role)
    {
        $this->authorize('update', RoleMenu::class);
        $this->assertRoleConfigurable($role);

        $catalog        = MenuCatalog::all();
        $roleLabels     = MenuCatalog::roleLabels();
        $currentKeys    = RoleMenu::getKeysForRole($role);
        $masterGroupKeys = MenuCatalog::MASTER_GROUP_KEYS;

        return view('role-menus.edit', compact(
            'role', 'catalog', 'roleLabels',
            'currentKeys', 'masterGroupKeys',
        ));
    }

    // ---------------------------------------------------------------
    // update — persist selection
    // ---------------------------------------------------------------

    public function update(Request $request, string $role)
    {
        $this->authorize('update', RoleMenu::class);
        $this->assertRoleConfigurable($role);

        $selected = (array) $request->input('menu_keys', []);

        RoleMenu::setKeysForRole($role, $selected);

        return redirect()
            ->route('role-menus.index')
            ->with('success', 'Menu untuk role "' . (MenuCatalog::roleLabels()[$role] ?? $role) . '" berhasil diperbarui.');
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    private function assertRoleConfigurable(string $role): void
    {
        if (! in_array($role, self::CONFIGURABLE_ROLES, true)) {
            abort(404, 'Role tidak ditemukan atau tidak dapat dikonfigurasi.');
        }
    }
}
