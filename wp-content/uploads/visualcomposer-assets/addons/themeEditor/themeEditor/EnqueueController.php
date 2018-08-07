<?php

namespace themeEditor\themeEditor;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Assets;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Request;
use VisualComposer\Helpers\Str;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use WP_Query;

class EnqueueController extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    public function __construct()
    {
        $this->wpAddAction('wp_head', 'wpHead', 0);
        $this->wpAddAction('wp_footer', 'wpFooterHeaderJs', 0);
        $this->wpAddAction('wp_footer', 'wpFooter', 20);
    }

    protected function wpHead(Options $optionsHelper, Frontend $frontendHelper, Request $requestHelper)
    {
        $sourceId = get_the_ID();
        if ($frontendHelper->isPreview()) {
            $preview = wp_get_post_autosave($sourceId);
            if (is_object($preview)) {
                $sourceId = $preview->ID;
            }
        }

        if (!$sourceId
            || in_array(
                $requestHelper->input('post_type'),
                ['vcv_sidebars', 'vcv_header', 'vcv_footer']
            )) {
            return;
        }

        foreach (['Header', 'Sidebar', 'Footer'] as $key) {
            if (in_array($requestHelper->input('vcv-template'), ['boxed', 'blank', 'default'])
                || (!$requestHelper->input('vcv-template')
                    && in_array(
                        get_post_meta($sourceId, '_' . VCV_PREFIX . 'page-template', true),
                        ['boxed', 'blank', 'default']
                    ))
                || $requestHelper->input(VCV_AJAX_REQUEST)
                || (!$requestHelper->input('vcv-template') && !get_post_meta($sourceId, '_' . VCV_PREFIX . 'page-template', true))
                || ($key === 'Sidebar'
                    && (in_array(
                        'header-footer-layout',
                        [
                            get_post_meta($sourceId, '_' . VCV_PREFIX . 'page-template', true),
                            $requestHelper->input('vcv-template'),
                        ]
                    )))) {
                continue;
            }

            $currentTemplateId = get_post_meta($sourceId, '_' . VCV_PREFIX . $key . 'TemplateId', true);
            $defaultTemplate = $optionsHelper->get('default' . 'vcv_' . strtolower($key) . 's');
            $currentTemplatePost = (intval($currentTemplateId) && $currentTemplateId > 0) ? get_post(
                $currentTemplateId
            )
                : false;
            $defaultTemplatePost = (intval($defaultTemplate) && $defaultTemplate > 0) ? get_post($defaultTemplate)
                : false;
            /** @see \themeEditor\themeEditor\EnqueueController::getQueryArgsByKey */
            $args = $this->call(
                'getQueryArgsByKey',
                [
                    $defaultTemplatePost,
                    $currentTemplatePost,
                    $defaultTemplate,
                    $currentTemplateId,
                    strtolower($key),
                ]
            );

            if (isset($args['p']) && $args['p'] > 0) {
                global $post;
                $query = new WP_Query($args);
                while ($query->have_posts()) {
                    $query->the_post();
                    $newPosts[] = $post;
                    do_action('wp_enqueue_scripts');
                    $this->call('enqueueAssets');

                    wp_reset_postdata();
                    break;
                }
            }
        }
    }

    protected function wpFooterHeaderJs()
    {
        /** @see \themeEditor\themeEditor\EnqueueController::enqueueScriptsByKey */
        $this->call('enqueueScriptsByKey', ['Header']);
    }

    protected function wpFooter()
    {
        foreach (['Sidebar', 'Footer'] as $key) {
            /** @see \themeEditor\themeEditor\EnqueueController::enqueueScriptsByKey */
            $this->call('enqueueScriptsByKey', [$key]);
        }
    }

    /**
     * @param $key
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     */
    protected function enqueueScriptsByKey($key, Options $optionsHelper, Frontend $frontendHelper)
    {
        $sourceId = get_the_ID();
        if ($frontendHelper->isPreview()) {
            $preview = wp_get_post_autosave($sourceId);
            if (is_object($preview)) {
                $sourceId = $preview->ID;
            }
        }
        $currentTemplateId = get_post_meta($sourceId, '_' . VCV_PREFIX . $key . 'TemplateId', true);
        $defaultTemplate = $optionsHelper->get('default' . 'vcv_' . strtolower($key) . 's');
        $currentTemplatePost = (intval($currentTemplateId) && $currentTemplateId > 0) ? get_post($currentTemplateId)
            : false;
        $defaultTemplatePost = (intval($defaultTemplate) && $defaultTemplate > 0) ? get_post($defaultTemplate)
            : false;
        /** @see \themeEditor\themeEditor\EnqueueController::getQueryArgsByKey */
        $args = $this->call(
            'getQueryArgsByKey',
            [
                $defaultTemplatePost,
                $currentTemplatePost,
                $defaultTemplate,
                $currentTemplateId,
                strtolower($key),
            ]
        );

        if (isset($args['p']) && $args['p'] > 0) {
            global $post;
            $query = new WP_Query($args);
            while ($query->have_posts()) {
                $query->the_post();
                $newPosts[] = $post;
                do_action('wp_print_footer_scripts');

                wp_reset_postdata();
                break;
            }
        }
    }

    /**
     * @param \VisualComposer\Helpers\Str $strHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Assets $assetsHelper
     */
    protected function enqueueAssets(Str $strHelper, Frontend $frontendHelper, Assets $assetsHelper)
    {
        $sourceId = get_the_ID();
        if ($frontendHelper->isPreview()) {
            $preview = wp_get_post_autosave($sourceId);
            if (is_object($preview)) {
                $sourceId = $preview->ID;
            }
        }
        $assetsFiles = get_post_meta($sourceId, 'vcvSourceAssetsFiles', true);

        if (!is_array($assetsFiles)) {
            return;
        }

        if (isset($assetsFiles['cssBundles']) && is_array($assetsFiles['cssBundles'])) {
            foreach ($assetsFiles['cssBundles'] as $asset) {
                wp_enqueue_style(
                    'vcv:assets:source:styles:' . $strHelper->slugify($asset),
                    $assetsHelper->getAssetUrl($asset),
                    [],
                    VCV_VERSION
                );
            }
            unset($asset);
        }

        if (isset($assetsFiles['jsBundles']) && is_array($assetsFiles['jsBundles'])) {
            foreach ($assetsFiles['jsBundles'] as $asset) {
                wp_enqueue_script(
                    'vcv:assets:source:scripts:' . $strHelper->slugify($asset),
                    $assetsHelper->getAssetUrl($asset),
                    [],
                    VCV_VERSION,
                    true
                );
            }
            unset($asset);
        }

        $this->call('enqueueSourceAssets');
    }

    /**
     * @param $defaultTemplatePost
     * @param $currentTemplatePost
     * @param $defaultTemplate
     * @param $currentTemplateId
     * @param $key
     * @param \VisualComposer\Helpers\Request $requestHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     *
     * @return array
     */
    protected function getQueryArgsByKey(
        $defaultTemplatePost,
        $currentTemplatePost,
        $defaultTemplate,
        $currentTemplateId,
        $key,
        Request $requestHelper,
        Frontend $frontendHelper
    ) {
        $args = [
            'post_type' => 'vcv_' . $key . 's',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];
        // @codingStandardsIgnoreLine
        if ($defaultTemplatePost && $defaultTemplatePost->post_status === 'publish'
            // @codingStandardsIgnoreLine
            && (!$currentTemplatePost || $currentTemplatePost->post_status !== 'publish')) {
            $args['p'] = $defaultTemplate;
            // @codingStandardsIgnoreLine
        } elseif ($currentTemplatePost && $currentTemplatePost->post_status === 'publish') {
            $args['p'] = $currentTemplateId;
        }

        if ($requestHelper->exists('vcv-' . $key)
            && $frontendHelper->isPageEditable()) {
            if ('default' === $requestHelper->input('vcv-' . $key)
                && $defaultTemplatePost
                // @codingStandardsIgnoreLine
                && $defaultTemplatePost->post_status === 'publish') {
                $args['p'] = $defaultTemplate;
            } else {
                $args['p'] = intval($requestHelper->input('vcv-' . $key));
            }
        }

        return $args;
    }

    /**
     * @param \VisualComposer\Helpers\Str $strHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Assets $assetsHelper
     */
    protected function enqueueSourceAssets(Str $strHelper, Frontend $frontendHelper, Assets $assetsHelper)
    {
        $sourceId = get_the_ID();
        $bundleUrl = get_post_meta($sourceId, 'vcvSourceCssFileUrl', true);
        if ($bundleUrl) {
            $version = get_post_meta($sourceId, 'vcvSourceCssFileHash', true);
            if (!preg_match('/^http/', $bundleUrl)) {
                if (!preg_match('/assets-bundles/', $bundleUrl)) {
                    $bundleUrl = '/assets-bundles/' . $bundleUrl;
                }
            }

            wp_enqueue_style(
                'vcv:assets:source:main:styles:' . $strHelper->slugify($bundleUrl),
                $assetsHelper->getAssetUrl($bundleUrl),
                [],
                VCV_VERSION . '.' . $version
            );
        }
    }
}
