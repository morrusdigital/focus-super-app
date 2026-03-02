<?php

namespace App\Support;

/**
 * Central registry of every navigable menu item in the application.
 *
 * Each item is keyed by a unique slug stored in `role_menus.menu_keys`.
 *
 * Item structure fields (all optional unless noted):
 *   label           (required) – display text shown in the sidebar
 *   icon            (required) – Feather icon name
 *   route           (required) – named Laravel route
 *   url_params               – query params array for URL generation (e.g. review_bp)
 *   active                   – routeIs() wildcard pattern for active-link detection
 *   active_url               – exact URL match for active detection (set at runtime)
 *   active_url_exclude       – URL to exclude from active detection (all_bp)
 *   group                    – 'master' → auto-grouped under "Master" submenu in sidebar
 *   order           (required) – integer, lower = higher in sidebar
 *   description              – short description shown in the role-menu admin UI
 *
 * ---
 * Special keys (not configurable via UI, always injected by SidebarMenu):
 *   role_management – Manajemen Role Menu  (holding_admin only, always visible)
 */
class MenuCatalog
{
    // ---------------------------------------------------------------
    // Keys that are locked to holding_admin only and never stored in DB
    // ---------------------------------------------------------------
    public const HOLDING_ADMIN_ONLY_KEYS = ['role_management'];

    // ---------------------------------------------------------------
    // Keys that form the "Master" submenu group in the sidebar
    // ---------------------------------------------------------------
    public const MASTER_GROUP_KEYS = ['bp_categories', 'chart_accounts', 'tax_masters'];

    // ---------------------------------------------------------------
    // Default menu key sets per configurable role.
    // Used by the seeder to populate initial DB values.
    // ---------------------------------------------------------------
    public const ROLE_DEFAULTS = [
        'company_admin'   => [
            'projects', 'task_projects', 'project_recap',
            'user_management',
            'submit_bp', 'my_bp',
            'bank_accounts', 'bp_categories', 'chart_accounts', 'tax_masters',
        ],
        'finance_holding' => [
            'projects', 'project_recap',
            'review_bp', 'all_bp',
        ],
        'finance_company' => [
            'projects',
            'submit_bp', 'my_bp',
            'bank_accounts', 'bp_categories', 'chart_accounts', 'tax_masters',
        ],
        'employee' => [
            'projects', 'task_projects',
            'my_tasks', 'overdue_tasks',
        ],
    ];

    // ---------------------------------------------------------------
    // Full catalog
    // ---------------------------------------------------------------

    /**
     * Returns the complete catalog of all configurable menu items.
     * Items are sorted by 'order' ascending.
     */
    public static function all(): array
    {
        static $items = null;

        if ($items === null) {
            $items = [
                'projects' => [
                    'label'       => 'Projects',
                    'icon'        => 'layers',
                    'route'       => 'projects.index',
                    'active'      => 'projects.*',
                    'order'       => 10,
                    'description' => 'Daftar project operasional (cashflow)',
                ],
                'task_projects' => [
                    'label'       => 'Task Projects',
                    'icon'        => 'check-circle',
                    'route'       => 'task-projects.index',
                    'active'      => 'task-projects.*',
                    'order'       => 20,
                    'description' => 'Manajemen task project mandiri',
                ],
                'project_recap' => [
                    'label'       => 'Rekap Project',
                    'icon'        => 'bar-chart-2',
                    'route'       => 'project-recaps.index',
                    'active'      => 'project-recaps.*',
                    'order'       => 30,
                    'description' => 'Laporan rekapitulasi project',
                ],
                'user_management' => [
                    'label'       => 'Manajemen User',
                    'icon'        => 'users',
                    'route'       => 'users.index',
                    'active'      => 'users.*',
                    'order'       => 40,
                    'description' => 'Kelola user dan akun dalam perusahaan',
                ],
                'review_bp' => [
                    'label'       => 'Review BP',
                    'icon'        => 'clipboard',
                    'route'       => 'budget-plans.index',
                    'url_params'  => ['status' => 'submitted'],
                    'active'      => 'budget-plans.index',
                    'order'       => 50,
                    'description' => 'Tinjau budget plan yang sudah diajukan',
                ],
                'all_bp' => [
                    'label'              => 'Semua BP',
                    'icon'               => 'list',
                    'route'              => 'budget-plans.index',
                    'active'             => 'budget-plans.index',
                    'active_url_exclude' => true, // resolved at render time
                    'order'              => 60,
                    'description'        => 'Lihat semua budget plan',
                ],
                'submit_bp' => [
                    'label'       => 'Ajukan BP',
                    'icon'        => 'plus-circle',
                    'route'       => 'budget-plans.create',
                    'active'      => 'budget-plans.create',
                    'order'       => 70,
                    'description' => 'Buat & ajukan budget plan baru',
                ],
                'my_bp' => [
                    'label'       => 'Daftar BP Saya',
                    'icon'        => 'file-text',
                    'route'       => 'budget-plans.index',
                    'active'      => 'budget-plans.index',
                    'order'       => 80,
                    'description' => 'Daftar budget plan milik sendiri',
                ],
                'bank_accounts' => [
                    'label'       => 'Rekening',
                    'icon'        => 'credit-card',
                    'route'       => 'bank-accounts.index',
                    'active'      => 'bank-accounts.*',
                    'order'       => 90,
                    'description' => 'Data rekening bank perusahaan',
                ],
                'bp_categories' => [
                    'label'       => 'Kategori BP',
                    'icon'        => 'tag',
                    'route'       => 'budget-plan-categories.index',
                    'active'      => 'budget-plan-categories.*',
                    'group'       => 'master',
                    'order'       => 100,
                    'description' => 'Kategori master budget plan',
                ],
                'chart_accounts' => [
                    'label'       => 'Akun',
                    'icon'        => 'book',
                    'route'       => 'chart-accounts.index',
                    'active'      => 'chart-accounts.*',
                    'group'       => 'master',
                    'order'       => 110,
                    'description' => 'Daftar akun (chart of accounts)',
                ],
                'tax_masters' => [
                    'label'       => 'Pajak',
                    'icon'        => 'percent',
                    'route'       => 'tax-masters.index',
                    'active'      => 'tax-masters.*',
                    'group'       => 'master',
                    'order'       => 120,
                    'description' => 'Master data pajak (PPh, PPN)',
                ],
                'my_tasks' => [
                    'label'       => 'My Tasks',
                    'icon'        => 'check-square',
                    'route'       => 'tasks.my',
                    'active'      => 'tasks.my',
                    'order'       => 130,
                    'description' => 'Task yang di-assign kepada saya',
                ],
                'overdue_tasks' => [
                    'label'       => 'Overdue Tasks',
                    'icon'        => 'alert-circle',
                    'route'       => 'tasks.overdue',
                    'active'      => 'tasks.overdue',
                    'order'       => 140,
                    'description' => 'Task yang sudah melewati due date',
                ],
            ];

            // Sort by order
            uasort($items, fn($a, $b) => $a['order'] <=> $b['order']);
        }

        return $items;
    }

    /**
     * Return catalog entry for a single key or null if not found.
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * Returns all keys that can be configured via the role-menu admin UI.
     * Excludes holding-admin-only items.
     */
    public static function configurableKeys(): array
    {
        return array_keys(self::all()); // all items in catalog are configurable
    }

    /**
     * Returns the holding_admin fixed entry — used by SidebarMenu.
     * Includes ALL catalog items + role_management (locked, not in catalog).
     */
    public static function holdingAdminItem(): array
    {
        return [
            'label'  => 'Manajemen Role Menu',
            'icon'   => 'shield',
            'route'  => 'role-menus.index',
            'active' => 'role-menus.*',
            'order'  => 45,
        ];
    }

    /**
     * Human-readable role labels for the admin UI.
     */
    public static function roleLabels(): array
    {
        return [
            'company_admin'   => 'Company Admin',
            'finance_holding' => 'Finance Holding',
            'finance_company' => 'Finance Company',
            'employee'        => 'Employee',
        ];
    }

    /**
     * Return items for a given set of keys, preserving catalog order.
     * Unknown keys are silently ignored.
     *
     * @param  string[]  $keys
     */
    public static function forKeys(array $keys): array
    {
        $catalog = self::all();
        $result  = [];

        foreach ($catalog as $key => $item) {
            if (in_array($key, $keys, true)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }
}
