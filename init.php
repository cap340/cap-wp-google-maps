<?php
/**
 * Plugin Name: Cap WP Google Maps
 * Plugin URI: https://cap340.fr/extensions/wordpress/cap-wpgm/
 * Description:
 * Version: 1.0.0
 * Author: Cap340
 * Author URI: https://cap340.fr
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Text Domain: cap-wpgm
 *
 * @todo description
 * @todo README.txt && LICENSE.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// get plugin data.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin_data    = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];

// define required constants.
! defined( 'CAP_WPGM_VERSION' ) && define( 'CAP_WPGM_VERSION', $plugin_version );
! defined( 'CAP_WPGM_URL' ) && define( 'CAP_WPGM_URL', plugin_dir_url( __FILE__ ) );
! defined( 'CAP_WPGM_DIR' ) && define( 'CAP_WPGM_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'CAP_WPGM_INC' ) && define( 'CAP_WPGM_INC', CAP_WPGM_DIR . 'includes/' );

// define required functions.
if ( ! function_exists( 'cap_wpgm_initialize' ) ) {
	/**
	 * Unique access to instance of the plugin class.
	 *
	 * @return Cap_WpGm
	 * @since 1.0.0
	 */
	function cap_wpgm_initialize() {
		// Load required classes and functions.
		require_once( CAP_WPGM_INC . 'class-cap-wpgm.php' );

		return cap_wpgm();
	}
}

// start plugin.
add_action( 'plugins_loaded', 'cap_wpgm_install', 11 );
if ( ! function_exists( 'cap_wpgm_install' ) ) {
	/**
	 * Install plugin and start the processing
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function cap_wpgm_install() {
		/**
		 * Instance main plugin class
		 */
		global $cap_wpgm;

		// load plugin text domain.
		load_plugin_textdomain( 'cap-wpgm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		$cap_wpgm = cap_wpgm_initialize();
	}
}

// uninstall plugin
register_deactivation_hook( __FILE__, 'cap_wpgm_deactivate' );
if ( ! function_exists( 'cap_wpgm_deactivate' ) ) {
	/**
	 * Uninstall plugin functions
	 *
	 * @since 1.0.0
	 */
	function cap_wpgm_deactivate() {
	}
}
