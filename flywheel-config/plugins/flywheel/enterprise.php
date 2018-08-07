<?php

function _alternate_admin_domain($url) {
	if( preg_match("/\/wp-admin\/|\/wp-login\.php|preview=true/", $url) ) {
		return preg_replace("/(^|\/\/)".FW_MAIN_DOMAIN."/", "$1".FW_ADMIN_DOMAIN, $url);
	}
	return preg_replace("/(^|\/\/)".FW_ADMIN_DOMAIN."/", "$1".FW_MAIN_DOMAIN, $url);
}
add_filter("login_url", "_alternate_admin_domain", 1);
add_filter("admin_url", "_alternate_admin_domain", 1);
add_filter("site_url", "_alternate_admin_domain", 1);
add_filter("preview_post_link", "_alternate_admin_domain", 1);
add_filter("preview_page_link", "_alternate_admin_domain", 1);

function auth_redirect() {
	if ( is_user_admin() ) {
		$scheme = 'logged_in';
	} else {
		$scheme = apply_filters( 'auth_redirect_scheme', '' );
	}

	if ( $user_id = wp_validate_auth_cookie( '',  $scheme) ) {
		do_action( 'auth_redirect', $user_id );

		if ( !$secure && get_user_option('use_ssl', $user_id) && false !== strpos($_SERVER['REQUEST_URI'], 'wp-admin') ) {
			if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
				wp_redirect( set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
				exit();
			} else {
				wp_redirect( 'https://' . FW_ADMIN_DOMAIN . $_SERVER['REQUEST_URI'] );
				exit();
			}
		}
		return;
	}

	// The cookie is no good so force login
	nocache_headers();
	$redirect = ( strpos( $_SERVER['REQUEST_URI'], '/options.php' ) && wp_get_referer() ) ? wp_get_referer() : set_url_scheme( 'http://' . FW_ADMIN_DOMAIN . $_SERVER['REQUEST_URI'] );
	$login_url = wp_login_url($redirect, true);
	wp_redirect($login_url);
	exit();
}
