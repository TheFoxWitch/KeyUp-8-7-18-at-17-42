<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Access\CurrentUser;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class DashboardController extends Container implements Module
{
    use WpFiltersActions;

    public function __construct()
    {
        $this->wpAddFilter('admin_menu', 'reorderSubMenu');
    }

    /**
     * Reorder vcv-settings submenu, move settings always first
     *
     * @param \VisualComposer\Helpers\Access\CurrentUser $currentUserAccess
     *
     * @throws \Exception
     */
    protected function reorderSubMenu(CurrentUser $currentUserAccess)
    {
        global $submenu;

        if (isset($submenu) && isset($submenu['vcv-settings']) && $currentUserAccess->wpAll('edit_pages')->get()) {
            foreach ($submenu['vcv-settings'] as $id => $meta) {
                if ($meta[0] === __('Settings', 'vcwb')) {
                    $submenu['vcv-settings'][ -1 ] = $meta;
                    unset($submenu['vcv-settings'][ $id ]);
                    ksort($submenu['vcv-settings']);
                }
            }
        }
    }
}
