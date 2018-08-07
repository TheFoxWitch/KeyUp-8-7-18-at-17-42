<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Hub\Addons;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Str;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class LayoutController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    protected $addonPath;

    public function __construct(Addons $addonsHelper)
    {
        $this->addonPath = rtrim($addonsHelper->getAddonRealPath('themeEditor'), '\\/');

        $this->wpAddAction(
            'wp',
            'addLayoutCss'
        );
    }

    protected function templatePath()
    {
        $outputResponse = $this->addonPath . '/views/layouts/';

        return $outputResponse;
    }

    protected function assetsPath()
    {
        $outputResponse = $this->addonPath . '/public/layouts/';

        return $outputResponse;
    }

    protected function addLayoutCss(
        Str $strHelper,
        Addons $addonsHelper,
        Request $requestHelper
    ) {
        $pageTemplate = false;
        if ($requestHelper->input('vcv-template-type', '') === 'vc-theme') {
            $pageTemplate = $requestHelper->input('vcv-template');
        } else {
            $currentTemplate = vcfilter('vcv:editor:settings:pageTemplatesLayouts:current', '');
            if (!empty($currentTemplate) && is_array($currentTemplate) && isset($currentTemplate['type'])
                && $currentTemplate['type'] === 'vc-theme') {
                $pageTemplate = $currentTemplate['value'];
            }
        }
        if ($pageTemplate
            && in_array(
                $pageTemplate,
                [
                    'header-footer-layout',
                    'header-footer-sidebar-layout',
                    'header-footer-sidebar-left-layout',
                ]
            )) {
            $addonUrl = $addonsHelper->getAddonUrl('themeEditor/themeEditor');
            $cssUrl = $addonUrl . '/public/layouts/css/bundle.min.css';
            wp_enqueue_style('vcv:theme:layout:bundle:css', $cssUrl);

            $file = $this->templatePath() . VCV_PREFIX . $pageTemplate . '.php';
            $fileName = basename($pageTemplate, '.php');
            $cssPath = $this->assetsPath() . ltrim('css/' . VCV_PREFIX . $fileName . '.min.css');
            if (file_exists($file) && file_exists($cssPath)) {
                $cssUrl = $addonUrl . '/public/layouts/css/' . VCV_PREFIX . $fileName . '.min.css';
                wp_enqueue_style('vcv:theme:layout:' . $strHelper->slugify($fileName) . ':css', $cssUrl);
            }
        }
    }
}
