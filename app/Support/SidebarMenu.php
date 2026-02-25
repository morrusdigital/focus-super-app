<?php

namespace App\Support;

use App\Models\User;

/**
 * Centralized sidebar menu builder.
 *
 * Each menu item can be:
 *   type = 'link'    → single menu link
 *   type = 'submenu' → collapsible parent with children
 *
 * Keys per item:
 *   label         (string)         Display text
 *   route         (string|null)    Named route
 *   url           (string|null)    Explicit URL (use when route has query params)
 *   icon          (string)         Feather icon name
 *   active        (string|null)    routeIs() pattern, e.g. 'projects.*'
 *   active_url    (string|null)    Exact URL match for active check (query-param routes)
 *   children      (array)          Sub-items for type=submenu
 *   active_child  (string|null)    routeIs pattern to activate parent submenu
 */
class SidebarMenu
{
    /**
     * Return the menu items visible to the given user.
     */
    public static function forUser(User $user): array
    {
        // Priority order: holding > company_admin > project_manager > member
        if ($user->isHoldingAdmin() || $user->isFinanceHolding()) {
            return static::holdingMenu();
        }

        if ($user->isCompanyAdmin() || $user->isAdminCompany()) {
            return static::companyAdminMenu();
        }

        if ($user->isProjectManager()) {
            return static::projectManagerMenu();
        }

        if ($user->isMember()) {
            return static::memberMenu();
        }

        return [];
    }

    // ---------------------------------------------------------------
    // Per-role menus
    // ---------------------------------------------------------------

    private static function holdingMenu(): array
    {
        return [
            [
                'type'   => 'link',
                'label'  => 'Projects',
                'route'  => 'projects.index',
                'icon'   => 'layers',
                'active' => 'projects.*',
            ],
            [
                'type'   => 'link',
                'label'  => 'Rekap Project',
                'route'  => 'project-recaps.index',
                'icon'   => 'bar-chart-2',
                'active' => 'project-recaps.*',
            ],
            [
                'type'       => 'link',
                'label'      => 'Review BP',
                'url'        => route('budget-plans.index', ['status' => 'submitted']),
                'icon'       => 'clipboard',
                'active'     => 'budget-plans.index',
                'active_url' => route('budget-plans.index', ['status' => 'submitted']),
            ],
            [
                'type'   => 'link',
                'label'  => 'Semua BP',
                'route'  => 'budget-plans.index',
                'icon'   => 'list',
                'active' => 'budget-plans.index',
                // active only when NOT the submitted filter
                'active_url_exclude' => route('budget-plans.index', ['status' => 'submitted']),
            ],
        ];
    }

    private static function companyAdminMenu(): array
    {
        return [
            [
                'type'   => 'link',
                'label'  => 'Ajukan BP',
                'route'  => 'budget-plans.create',
                'icon'   => 'plus-circle',
                'active' => 'budget-plans.create',
            ],
            [
                'type'   => 'link',
                'label'  => 'Daftar BP Saya',
                'route'  => 'budget-plans.index',
                'icon'   => 'file-text',
                'active' => 'budget-plans.index',
            ],
            [
                'type'   => 'link',
                'label'  => 'Projects',
                'route'  => 'projects.index',
                'icon'   => 'layers',
                'active' => 'projects.*',
            ],
            [
                'type'   => 'link',
                'label'  => 'Rekap Project',
                'route'  => 'project-recaps.index',
                'icon'   => 'bar-chart-2',
                'active' => 'project-recaps.*',
            ],
            [
                'type'   => 'link',
                'label'  => 'Rekening',
                'route'  => 'bank-accounts.index',
                'icon'   => 'credit-card',
                'active' => 'bank-accounts.*',
            ],
            [
                'type'         => 'submenu',
                'label'        => 'Master',
                'icon'         => 'folder',
                'active_child' => 'budget-plan-categories.*,chart-accounts.*,tax-masters.*',
                'children'     => [
                    [
                        'label'  => 'Kategori BP',
                        'route'  => 'budget-plan-categories.index',
                        'active' => 'budget-plan-categories.*',
                    ],
                    [
                        'label'  => 'Akun',
                        'route'  => 'chart-accounts.index',
                        'active' => 'chart-accounts.*',
                    ],
                    [
                        'label'  => 'Pajak',
                        'route'  => 'tax-masters.index',
                        'active' => 'tax-masters.*',
                    ],
                ],
            ],
        ];
    }

    private static function projectManagerMenu(): array
    {
        return [
            [
                'type'   => 'link',
                'label'  => 'Projects',
                'route'  => 'projects.index',
                'icon'   => 'layers',
                'active' => 'projects.*',
            ],
        ];
    }

    private static function memberMenu(): array
    {
        return [
            [
                'type'   => 'link',
                'label'  => 'Projects',
                'route'  => 'projects.index',
                'icon'   => 'layers',
                'active' => 'projects.*',
            ],
            [
                'type'   => 'link',
                'label'  => 'My Tasks',
                'route'  => 'tasks.my',
                'icon'   => 'check-square',
                'active' => 'tasks.my',
            ],
            [
                'type'   => 'link',
                'label'  => 'Overdue Tasks',
                'route'  => 'tasks.overdue',
                'icon'   => 'alert-circle',
                'active' => 'tasks.overdue',
            ],
        ];
    }
}
