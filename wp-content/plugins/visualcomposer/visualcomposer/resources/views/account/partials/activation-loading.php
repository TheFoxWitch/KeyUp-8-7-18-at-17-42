<?php
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/** @var $controller \VisualComposer\Modules\Account\Pages\ActivationPage */
?>
<!-- Third screen / loading screen -->
<div class="vcv-popup-content vcv-popup-loading-screen">
    <!-- Loading image -->
    <div class="vcv-loading-dots-container">
        <div class="vcv-loading-dot vcv-loading-dot-1"></div>
        <div class="vcv-loading-dot vcv-loading-dot-2"></div>
    </div>

    <?php if ($controller->getActivePage() !== 'last') : ?>
        <div class="vcv-loading-text">
            <span class="vcv-popup-loading-heading"><?php
                echo esc_html__('Activating your copy ... Please wait.', 'vcwb');
                ?></span>
            <span class="vcv-popup-helper"><?php
                echo esc_html__('Don’t close this window while activation is in the process.', 'vcwb');
                ?></span>
            <?php if (vcvenv('VCV_FT_POST_UPDATE')) : ?>
                <div data-vcv-skip-post class="vcv-button-container vcv-skip-post-update-block vcv-popup--hidden">
                    <button data-vcv-skip-post-control class="vcv-popup-button vcv-popup-form-submit vcv-popup-form-update">
                <span>
                    <?php echo esc_html__('Skip this post', 'vcwb'); ?>
                </span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <!-- Loading big white circle -->
    <div class="vcv-popup-loading-zoom"></div>
</div>
