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
		add_shortcode( 'woo3dprints', array( 'Woo3Dprint', 'shortcode_woo3dprint') );

		// Action for slice
		add_action( 'woo3dprint_slicers', array( 'Woo3Dprint', 'slicers' ), 10, 1 );
		// add_action( 'woo3dprint_slice_fdm', array( 'Woo3Dprint', 'auto_check_update_meta' ), 10, 2 );
		// add_filter( 'preprocess_comment', array( 'Woo3Dprint', 'auto_check_comment' ), 1 );
	}

	public static function shortcode_woo3dprint() {
		require_once plugin_dir_path( __FILE__ ) . 'views/shortcodes/woo3dprint.php';
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

	/**
	 * Upload files
	 */
	public function upload_any_file($temporary = false) {
		if (!function_exists('wp_handle_upload')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		// echo $_FILES["upload"]["name"];
		$upload_overrides['test_form'] = false;
		if($temporary)
			$upload_overrides['remove_30_days'] = true;
		
		$files = $_FILES['file'];
		foreach ($files['name'] as $key => $value) {
			if ($files['name'][$key]) {
				$file = array(
					'name'     => $files['name'][$key],
					'type'     => $files['type'][$key],
					'tmp_name' => $files['tmp_name'][$key],
					'error'    => $files['error'][$key],
					'size'     => $files['size'][$key]
				);
				$movefile = wp_handle_upload($file, $upload_overrides);
				if ($movefile && !isset($movefile['error'])) {
					$return['data'] = $movefile['file'];
					$return['status'] = 'success';
					$return['url'] = $movefile['url'];
					echo json_encode($return);
				} else {
					echo json_encode($movefile['error']);
				}
			}
		}
		wp_die();
	}
}
