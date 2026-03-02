<?php

namespace Database\Seeders;

use App\Support\MenuCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the role_menus table with default menu key sets
 * for each configurable role.
 *
 * Safe to re-run: uses INSERT OR IGNORE (upsert) logic.
 */
class RoleMenuSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (MenuCatalog::ROLE_DEFAULTS as $role => $keys) {
            DB::table('role_menus')->upsert(
                [
                    'role'       => $role,
                    'menu_keys'  => json_encode($keys),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                ['role'],          // unique key
                ['menu_keys', 'updated_at'],  // columns to update on conflict
            );
        }
    }
}
