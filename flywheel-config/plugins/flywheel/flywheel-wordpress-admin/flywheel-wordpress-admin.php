<?php
/*
Plugin Name:  Flywheel WordPress Admin
Description:  Replaces content within wp-admin to reflect how WordPress functions in Flywheel's environment
Plugin URI:   https://getflywheel.com
Version:      1.0
Author:       Flywheel
License:      GPL v2 or later
Network:      true

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

function fw_enqueue_scripts($hook) {
	if ( defined( 'FW_WHITE_LABEL' ) && FW_WHITE_LABEL ) {
		if ( 'update-core.php' == $hook ) {
			wp_enqueue_script( 'update_core', '/flywheel-config/plugins/flywheel/flywheel-wordpress-admin/update-core-white-label.js' );
		} else if ( 'options-general.php' == $hook ) {
			wp_enqueue_script( 'options_general', '/flywheel-config/plugins/flywheel/flywheel-wordpress-admin/options-general-white-label.js' );
		}
	}
	else {
		if ( 'update-core.php' == $hook ) {
			wp_enqueue_script( 'update_core', '/flywheel-config/plugins/flywheel/flywheel-wordpress-admin/update-core.js' );
		} else if ( 'options-general.php' == $hook ) {
			wp_enqueue_script( 'options_general', '/flywheel-config/plugins/flywheel/flywheel-wordpress-admin/options-general.js' );
		}
	}
}

add_action( 'admin_enqueue_scripts', 'fw_enqueue_scripts' );
