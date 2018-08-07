<?php

    // Load the TGM init if it exists
    if ( file_exists( get_parent_theme_file_path('/admin/tgm/tgm-init.php' )) ) {
        require_once get_parent_theme_file_path('/admin/tgm/tgm-init.php');
    }

    // Load the embedded Redux Framework
    if ( file_exists( get_parent_theme_file_path('/admin/redux-framework/framework.php' )) ) {
        require_once get_parent_theme_file_path('/admin/redux-framework/framework.php');
    }

    // Load the theme/plugin options
    if ( file_exists(get_parent_theme_file_path('/admin/options-init.php' )) ) {
        require_once get_parent_theme_file_path('/admin/options-init.php');
    }

    // Load Redux extensions
    if ( file_exists( get_parent_theme_file_path('/admin/redux-extensions/extensions-init.php' )) ) {
        require_once get_parent_theme_file_path('/admin/redux-extensions/extensions-init.php');
    }
