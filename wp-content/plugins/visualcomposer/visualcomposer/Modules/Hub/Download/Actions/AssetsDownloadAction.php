<?php

namespace VisualComposer\Modules\Hub\Download\Actions;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Modules\Hub\Download\Actions\Traits\Action;

class AssetsDownloadAction extends Container implements Module
{
    protected $helperName = 'HubActionsSharedLibrariesBundle';

    protected $actionName = 'assets';

    use Action;
}
