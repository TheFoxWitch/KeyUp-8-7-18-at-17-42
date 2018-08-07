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
use VisualComposer\Helpers\Hub\Elements;
use VisualComposer\Helpers\Logger;
use VisualComposer\Helpers\Traits\EventsFilters;

class ElementsUpdater extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        if (!vcvenv('VCV_ENV_DEV_ELEMENTS')) {
            /** @see \VisualComposer\Modules\Hub\Download\ElementsUpdater::updateElements */
            $this->addFilter('vcv:hub:download:bundle vcv:hub:download:bundle:element/*', 'updateElements');
        }
    }

    protected function updateElements($response, $payload, Logger $loggerHelper, Elements $elementsHelper)
    {
        $bundleJson = isset($payload['archive']) ? $payload['archive'] : false;
        if (vcIsBadResponse($response) || !$bundleJson || is_wp_error($bundleJson)) {
            $this->logErrors($response, $loggerHelper, $bundleJson);

            return ['status' => false];
        }
        $hubHelper = vchelper('HubElements');
        /** @var Differ $elementsDiffer */
        $hubElements = $hubHelper->getElements(true);

        $elementsDiffer = vchelper('Differ');
        if (!empty($hubElements)) {
            $elementsDiffer->set($hubElements);
        }

        $fileHelper = vchelper('File');
        $fileHelper->createDirectory($hubHelper->getElementPath());
        $elementsDiffer->onUpdate(
            [$hubHelper, 'updateElement']
        )->set(
            $bundleJson['elements']
        );
        $elements = $elementsDiffer->get();
        $hubHelper->setElements($elements);
        if (!isset($response['elements']) || !is_array($response['elements'])) {
            $response['elements'] = [];
        }
        $elementKeys = array_keys($bundleJson['elements']);
        foreach ($elementKeys as $element) {
            $elementData = $elements[ $element ];
            $elementData['tag'] = $element;
            $elementData['assetsPath'] = $elementsHelper->getElementUrl($elementData['assetsPath']);
            $elementData['bundlePath'] = $elementsHelper->getElementUrl($elementData['bundlePath']);
            $elementData['elementPath'] = $elementsHelper->getElementUrl($elementData['elementPath']);

            if (isset($elementData['settings']['metaThumbnailUrl'])) {
                $elementData['settings']['metaThumbnailUrl'] = $elementsHelper->getElementUrl(
                    $elementData['settings']['metaThumbnailUrl']
                );
            }
            if (isset($elementData['settings']['metaPreviewUrl'])) {
                $elementData['settings']['metaPreviewUrl'] = $elementsHelper->getElementUrl(
                    $elementData['settings']['metaPreviewUrl']
                );
            }

            $response['elements'][] = $elementData;
        }

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
        $messages[] = __('Failed to update elements', 'vcwb') . ' #10046';

        if (is_wp_error($response)) {
            /** @var \WP_Error $response */
            $messages[] = implode('. ', $response->get_error_messages()) . ' #10047';
        } elseif (is_array($response) && isset($response['body'])) {
            // @codingStandardsIgnoreLine
            $resultDetails = @json_decode($response['body'], 1);
            if (is_array($resultDetails) && isset($resultDetails['message'])) {
                $messages[] = $resultDetails['message'] . ' #10048';
            }
        }
        if (is_wp_error($bundleJson)) {
            /** @var \WP_Error $bundleJson */
            $messages[] = implode('. ', $bundleJson->get_error_messages()) . ' #10049';
        } elseif (is_array($bundleJson) && isset($bundleJson['body'])) {
            // @codingStandardsIgnoreLine
            $resultDetails = @json_decode($bundleJson['body'], 1);
            if (is_array($resultDetails) && isset($resultDetails['message'])) {
                $messages[] = $resultDetails['message'] . ' #10050';
            }
        }

        $loggerHelper->log(
            implode('. ', $messages),
            [
                'response' => is_wp_error($response) ? 'wp error' : $response,
                'bundleJson' => is_wp_error($bundleJson) ? 'wp error' : $bundleJson,
            ]
        );
    }
}
