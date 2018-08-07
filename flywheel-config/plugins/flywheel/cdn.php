<?php
/*
Plugin Name: WP Stack CDN
Version: 0.2
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

define( 'WP_STAGE', 'production' );

// Uncomment to test exclusion logic
// define( 'FLYWHEEL_CDN_EXCLUSIONS', '(.jpe?g)' );

// Convenience methods
if(!class_exists('WP_Stack_Plugin')){class WP_Stack_Plugin{function hook($h){$p=10;$m=$this->sanitize_method($h);$b=func_get_args();unset($b[0]);foreach((array)$b as $a){if(is_int($a))$p=$a;else $m=$a;}return add_action($h,array($this,$m),$p,999);}private function sanitize_method($m){return str_replace(array('.','-'),array('_DOT_','_DASH_'),$m);}}}

/***
 * This plugin will replace all instances of the primary domain in assets with the CDN domain if a user has enabled their CDN.
 */
class WP_Stack_CDN_Plugin extends WP_Stack_Plugin {

	/**
	 * The current state of the WP_Stack_CDN_Plugin class.
	 *
	 * @var string
	 */
	public static $instance;

	/**
	 * A string with the current primary domain set on Flywheel.
	 *
	 * @var string
	 */
	public $site_domain;

	/**
	 * A string with the current CDN set on Flywheel from MaxCDN.
	 *
	 * @var string
	 */
	public $cdn_domain;

	/**
	 * A string for the current path on the server where images are uploaded.
	 *
	 * @var string
	 */
	public $upload_dir;

	/**
	 * A variable to help fine-tune what content should be served from the CDN.
	 *
	 * @var boolean
	 */
	public $uploads_only;

	/**
	 * The array of extensions that can be served from MaxCDN.
	 *
	 * @var array
	 */
	public $extensions;

	/**
	 * The current protocol - if the user has TLS/SSL or not.
	 * Should be https:// or http://
	 *
	 * @var string
	 */
	public $protocol;

	/**
	 * Create a new WP_Stack_CDN_Plugin instance.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$instance = $this;
		$this->hook( 'plugins_loaded' );
	}

	/**
	 * Load all the settings from Flywheel configuration.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		$domain_set_up = get_option( 'wp_stack_cdn_domain' ) || ( defined( 'WP_STACK_CDN_DOMAIN' ) && WP_STACK_CDN_DOMAIN );
		$production = defined( 'WP_STAGE' ) && WP_STAGE === 'production';
		$staging = defined( 'WP_STAGE' ) && WP_STAGE === 'staging';
		$uploads_only = defined( 'WP_STACK_CDN_UPLOADS_ONLY' ) && WP_STACK_CDN_UPLOADS_ONLY;
		if ( $domain_set_up && !$staging && ( $production || $uploads_only ) )
			$this->hook( 'init' );
	}

	/**
	 * Setup and initialize the CDN work.
	 *
	 * @return void
	 */
	public function init() {
		$this->uploads_only = apply_filters( 'wp_stack_cdn_uploads_only', defined( 'WP_STACK_CDN_UPLOADS_ONLY' ) ? WP_STACK_CDN_UPLOADS_ONLY : false );
		// On current gen, by default we don't push large files to CDN for bandwidth costs
		$extensions = array( 'jpe?g', 'gif', 'png', 'css', 'bmp', 'js', 'ico', 'mp3', 'svg' );
		if( defined('FLYWHEEL_CDN_MEDIA') && FLYWHEEL_CDN_MEDIA == TRUE ) {
			// But it is possible, and makes sense on next gen.
			$extensions = array_merge($extensions, array( 'webp', 'mp4', 'webm', 'mov', 'mpe?g', 'mkv', 'ogg', 'ogv', 'oga', 'flac' ) );
		}
		$this->extensions = apply_filters( 'wp_stack_cdn_extensions', $extensions );
		if ( !is_admin() ) {
			$this->hook( 'template_redirect' );

			if ( $this->uploads_only ) {
				$this->hook( 'wp_stack_cdn_content', 'filter_uploads_only' );
			}
			else {
				$this->hook( 'wp_stack_cdn_content', 'filter' );
			}

			// srcset only works on items uploaded through Wordpress, so it's okay to put this hook outside of the if statement.
			$this->hook( 'wp_stack_cdn_content', 'filter_srcset' );

			$this->site_domain = parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
			$this->cdn_domain = defined( 'WP_STACK_CDN_DOMAIN' ) ? WP_STACK_CDN_DOMAIN : get_option( 'wp_stack_cdn_domain' );
			$this->protocol = (empty($_SERVER["HTTPS"])) ? "http://" : "https://";
		}
	}

	/**
	 * This function parses through $content, finds asset url's within
	 * double quotes, replaces all instances of the Flywheel Primary
	 * domain with the MaxCDN domain.
	 *
	 * This function is looking for asset URL's that are located exclusively
	 * inside of the uploads folder (Not under themes or plugins)
	 *
	 * @return string $content the CDN-ified $content string.
	 */
	public function filter_uploads_only( $content ) {
		$upload_dir = wp_upload_dir();
		$upload_dir = $upload_dir['baseurl'];
		$domain = preg_quote( parse_url( $upload_dir, PHP_URL_HOST ), '#' );
		$path = parse_url( $upload_dir, PHP_URL_PATH );
		$preg_path = preg_quote( $path, '#' );
		$pattern = "#=([\"'])(https?://{$domain})?$preg_path/((?:(?!\\1).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#";

		if ( defined( "FLYWHEEL_CDN_EXCLUSIONS" ) ) {
			return preg_replace_callback(
				$pattern,
				function($matches) {
					if ( preg_match(FLYWHEEL_CDN_EXCLUSIONS, $matches[0]) ) {
						return $matches[0];
					}
					$pattern = "#=([\"'])(https?://{$domain})?$preg_path/((?:(?!\\1).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#";
					return preg_replace( $pattern, '=$1' . $this->protocol . $this->cdn_domain . $path . '/$3.$4$5$1', $content );
				},
				$content
				);
		}

		// Targeted replace just on uploads URLs
		return preg_replace( $pattern, '=$1' . $this->protocol . $this->cdn_domain . $path . '/$3.$4$5$1', $content );
	}

	/**
	 * This function parses through $content, finds asset url's within
	 * double quotes, and replaces all instances of the Flywheel Primary
	 * domain with the MaxCDN domain.
	 *
	 * This function is looking for asset URL's that are inside Plugins,
	 * Themes, and Uploads.
	 *
	 * @return string $content the CDN-ified $content string.
	 */
	public function filter( $content ) {
		$pattern = "#=([\"'])((https?)://{$this->site_domain})?/([^/](?:(?!\\1).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#";

		if ( defined("FLYWHEEL_CDN_EXCLUSIONS") ) {
			return preg_replace_callback(
				$pattern,
				function($matches) {
					if ( preg_match(FLYWHEEL_CDN_EXCLUSIONS, $matches[0]) ) {
						return $matches[0];
					}
					$pattern = "#=([\"'])((https?)://{$this->site_domain})?/([^/](?:(?!\\1).)+)\.(" . implode( '|', $this->extensions ) . ")(\?((?:(?!\\1).)+))?\\1#";
					return preg_replace( $pattern, '=$1' . $this->protocol . $this->cdn_domain . '/$4.$5$6$1', $matches[0] );
				},
				$content
				);
		}

		return preg_replace( $pattern, '=$1' . $this->protocol . $this->cdn_domain . '/$4.$5$6$1', $content );
	}


	/**
	 * This function parses through $content, finds instances of srcset,
	 * and replaces all instances of the Flywheel Primary domain with
	 * the MaxCDN domain.
	 *
	 * This function is looking for asset URL's that are inside Plugins,
	 * Themes, and Uploads.
	 *
	 * @return string $content the CDN-ified $content string.
	 */
	public function filter_srcset( $content ) {

		// Find all the srcsets in the document, store in $srcset_attrs.
		preg_match_all( '/(srcset)=("[^"]*")/i', $content, $srcset_attrs );

		// Loops though the attributes
		foreach ( $srcset_attrs[0] as $srcset_attr ) {

			$new_srcset_attr = preg_replace("((https?)://{$this->site_domain})", $this->protocol . $this->cdn_domain, $srcset_attr);

			// if there are exclusions setup (must be reg ex syntax)
			if ( defined("FLYWHEEL_CDN_EXCLUSIONS") ) {

				// If the match doesn't hit the new srcset attribute,
				// replace the old srcset with the new.
				if ( ! preg_match(FLYWHEEL_CDN_EXCLUSIONS, $new_srcset_attr) ) {
					$content = str_replace($srcset_attr, $new_srcset_attr, $content);
				}

			} else {
				$content = str_replace($srcset_attr, $new_srcset_attr, $content);
			}

		}

		return $content;
	}

	/**
	 * Starts an output buffer, and sends what's returned to the ob function below.
	 *
	 * @return void
	 */
	public function template_redirect() {
		ob_start( array( $this, 'ob' ) );
	}

	/**
	 * template_redirect is sent to this function after the buffer starts - it applies the
	 * wp_stack_cdn_content hooks - those call filter, filter_uploads_only, and filter_srcset.
	 *
	 * @return the filtered $contents
	 */
	public function ob( $contents ) {
		return apply_filters( 'wp_stack_cdn_content', $contents, $this );
	}
}

new WP_Stack_CDN_Plugin;
