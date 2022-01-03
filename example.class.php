<?php

class Woo3Dprint {
	// const API_HOST = 'rest.akismet.com';
	// const API_PORT = 80;
	// const MAX_DELAY_BEFORE_MODERATION_EMAIL = 86400; // One day in seconds

	private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;

		// Shortcode views
		add_shortcode( 'woo3dprint', array( 'Woo3Dprint', 'shortcode_woo3dprint') );

		// Action for slice
		add_action( 'woo3dprint_slicers', array( 'Woo3Dprint', 'slicers' ), 10, 1 );
		// add_action( 'woo3dprint_slice_fdm', array( 'Woo3Dprint', 'auto_check_update_meta' ), 10, 2 );
		// add_filter( 'preprocess_comment', array( 'Woo3Dprint', 'auto_check_comment' ), 1 );
	}

	public function shortcode_woo3dprint() {
		return apply_filters( 'akismet_get_api_key', defined('WPCOM_API_KEY') ? constant('WPCOM_API_KEY') : get_option('wordpress_api_key') );
	}
	
	// Slicers Init
	public function slicers($args) {
		if($args['type'] == 'lcd')
			return self::slice_lcd($args);
		return self::slice_fdm($args);
	}
	
	public function slice_fdm($args) {

	}

	public function slice_lcd($args) {

	}
}
