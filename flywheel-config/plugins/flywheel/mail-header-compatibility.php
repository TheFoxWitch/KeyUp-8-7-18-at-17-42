<?php
/*
 * Plugin Name: Mail Header Compatibility by Flywheel
 * Plugin UI: https://getFlywheel.com
 * Description: Allows emails with malformed headers to be sent.
 * Author: Flywheel
 * Version: 0.1.0
 * Author URI: https://getFlywheel.com
 */

if ( defined( 'FW_MAIL_HEADER_COMPATIBILITY' ) && FW_MAIL_HEADER_COMPATIBILITY ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	if ( is_plugin_active('jetpack/jetpack.php') ) {
		add_filter( 'wp_mail', 'flywheel_demangle_mail_headers' );

		/**
		 * Reformats mail headers to remove quoted strings.
		 *
		 * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
		 *                    subject, message, headers, and attachments values.
		 * @return array The sanitized arguments for wp_mail().
		 */
		function flywheel_demangle_mail_headers( $args ) {
			$headers = explode( "\n", trim( $args['headers'] ) );

			foreach ( $headers as $index => $header_row ) {
				list( $header, $value ) = explode( ':', $header_row, 2 );

				if ( $header == 'From' || $header == 'Reply-To' ) {
					$value = str_replace( '"', '', $value );
				}

				$headers[$index] = implode( ':', array( $header, $value ) );
			}

			$args['headers'] = implode( "\n", $headers );

			if ( $args['to'] ) {
				foreach ( $args['to'] as $index => $to ) {
					$args['to'][$index] = str_replace( '"', '', $to );
				}
			}

			return $args;
		}
	}
}
