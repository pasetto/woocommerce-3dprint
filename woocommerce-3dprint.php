<?php
/*
Plugin Name: Woocommerce 3D Print (Nano Design)
Plugin URI: #
Description: Impressão 3D
Version: 0.0.1
Author: Nano Design Impressão 3D
Author URI: #
License: GPLv2 or later
Text Domain: woo3dprint
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'WOO3DPRINT_VERSION', '0.0.1' );
define( 'WOO3DPRINT__MINIMUM_WP_VERSION', '5.0' );
define( 'WOO3DPRINT__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOO3DPRINT_DELETE_LIMIT', 100000 );
define( 'WOO3DPRINT__CURAENGINE_PATH', plugin_dir_path( __FILE__ ) . "_inc/vendor/Cura2/windows/CuraEngine.exe" );

// register_activation_hook( __FILE__, array( 'Woo3Dprint', 'plugin_activation' ) );
// register_deactivation_hook( __FILE__, array( 'Woo3Dprint', 'plugin_deactivation' ) );

require_once( WOO3DPRINT__PLUGIN_DIR . 'class.woo3dprint.php' );
require_once( WOO3DPRINT__PLUGIN_DIR . 'class.woo3dprint-slicer-fdm.php' );
// require_once( WOO3DPRINT__PLUGIN_DIR . 'class.akismet-rest-api.php' );

add_action( 'init', array( 'Woo3Dprint', 'init' ) );
add_action( 'init', array( 'Woo3Dprint_Slicer_Fdm', 'init' ) );

// add_action( 'rest_api_init', array( 'Akismet_REST_API', 'init' ) );

// if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
// 	require_once( WOO3DPRINT__PLUGIN_DIR . 'class.akismet-admin.php' );
// 	add_action( 'init', array( 'Akismet_Admin', 'init' ) );
// }

// //add wrapper class around deprecated akismet functions that are referenced elsewhere
// require_once( WOO3DPRINT__PLUGIN_DIR . 'wrapper.php' );

// if ( defined( 'WP_CLI' ) && WP_CLI ) {
// 	require_once( WOO3DPRINT__PLUGIN_DIR . 'class.akismet-cli.php' );
// }
