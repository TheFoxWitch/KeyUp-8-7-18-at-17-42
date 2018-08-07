<?php
/*
Plugin Name: Flywheel
Plugin URI: http://getFlywheel.com
Description: Plugins and configuration required by Flywheel.
Version: 0.1.0
Author: Flywheel
Author URI: http://getFlywheel.com
License: BSD
*/

if (!function_exists('wp_new_blog_notification')) {
	function wp_new_blog_notification($blog_title, $blog_url, $user_id, $password) {
		return;
	}
}



class FlywheelNginxCompat {
	protected $have_nginx;

	public static function instance() {
		static $self = false;
		if (!$self) {
			$self = new FlywheelNginxCompat();
		}
		return $self;
	}

	private function __construct() {
		$this->have_nginx = true;
		if ($this->have_nginx) {
			add_filter('got_rewrite', array($this, 'got_rewrite'), 999);
			// For compatibility with several plugins and nginx HTTPS proxying schemes
			if (empty($_SERVER['HTTPS']) || 'off' == $_SERVER['HTTPS']) {
				unset($_SERVER['HTTPS']);
			}
		}
	}

	public function got_rewrite($got) {
		return true;
	}

	public function haveNginx() {
		return $this->have_nginx;
	}
}
$nginx_compat = FlywheelNginxCompat::instance();
