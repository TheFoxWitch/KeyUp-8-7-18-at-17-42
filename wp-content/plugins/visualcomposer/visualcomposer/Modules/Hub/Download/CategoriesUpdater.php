<?php

namespace VisualComposer\Modules\Hub\Download;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Differ;
use VisualComposer\Helpers\Logger;
use VisualComposer\Helpers\Traits\EventsFilters;

class CategoriesUpdater extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        /** @see \VisualComposer\Modules\Hub\Download\CategoriesUpdater::updateCategories */
        $this->addFilter('vcv:hub:download:bundle vcv:hub:download:bundle:categories', 'updateCategories');
    }

    protected function updateCategories($response, $payload, Logger $loggerHelper)
    {
        $bundleJson = isset($payload['archive']) ? $payload['archive'] : false;
        if (vcIsBadResponse($response) || !$bundleJson || is_wp_error($bundleJson)) {
            $this->logErrors($response, $loggerHelper, $bundleJson);

            return ['status' => false];
        }
        $hubBundleHelper = vchelper('HubActionsCategoriesBundle');
        $hubHelper = vchelper('HubCategories');
        /** @var Differ $categoriesDiffer */
        $hubCategories = $hubHelper->getCategories();

        $categoriesDiffer = vchelper('Differ');
        if (!empty($hubCategories)) {
            $categoriesDiffer->set($hubCategories);
        }

        $fileHelper = vchelper('File');
        $fileHelper->createDirectory($hubHelper->getCategoriesPath());
        $fileHelper->copyDirectory(
            $hubBundleHelper->getTempBundleFolder('categories'),
            $hubHelper->getCategoriesPath(),
            false
        );
        $categoriesDiffer->onUpdate(
            [$hubHelper, 'updateCategory']
        )->set(
            $bundleJson['categories']
        );
        $hubHelper->setCategories($categoriesDiffer->get());

        return $response;
    }

    /**
     * @param $response
     * @param \VisualComposer\Helpers\Logger $loggerHelper
     * @param $bundleJson
     */
    protected function logErrors($response, Logger $loggerHelper, $bundleJson)
    {
        $messages = [];
        $messages[] = __('Failed to update categories', 'vcwb') . ' #10037';

        if (is_wp_error($response)) {
            /** @var \WP_Error $response */
            $messages[] = implode('. ', $response->get_error_messages()) . ' #10038';
        } elseif (is_array($response) && isset($response['body'])) {
            // @codingStandardsIgnoreLine
            $resultDetails = @json_decode($response['body'], 1);
            if (is_array($resultDetails) && isset($resultDetails['message'])) {
                $messages[] = $resultDetails['message'] . ' #10039';
            }
        }
        if (is_wp_error($bundleJson)) {
            /** @var \WP_Error $bundleJson */
            $messages[] = implode('. ', $bundleJson->get_error_messages()) . ' #10040';
        } elseif (is_array($bundleJson) && isset($bundleJson['body'])) {
            // @codingStandardsIgnoreLine
            $resultDetails = @json_decode($bundleJson['body'], 1);
            if (is_array($resultDetails) && isset($resultDetails['message'])) {
                $messages[] = $resultDetails['message'] . ' #10041';
            }
        }

        $loggerHelper->log(
            implode('. ', $messages),
            [
                'response' => is_wp_error($response) ? 'wp error' : $response,
                'bundleJson' => is_wp_error($bundleJson) ? 'wp error' : $response,
            ]
        );
    }
}
