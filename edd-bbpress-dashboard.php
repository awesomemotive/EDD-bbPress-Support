<?php
/*
 * Plugin Name: EDD bbPress Support Dashboard
 * Description: Support dashboard for sites running EDD and bbPress
 * Author: Pippin Williamson and Sunny Ratilal
 * Author URI: https://easydigitaldownloads.com/
 * Version: 2.0
 */

function edd_bbp_d_activate() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/core-options.php';
	do_action( 'edd_bbp_d_activation' );
}
register_activation_hook( __FILE__ , 'edd_bbp_d_activate' );

/**
 * Setup the plugin
 */
function edd_bbp_d_setup() {
	edd_bbp_d_define_constants();
	edd_bbp_d_includes();
}
add_action( 'plugins_loaded', 'edd_bbp_d_setup' );

/**
 * Setup the globals
 */
function edd_bbp_d_define_constants() {
	define( 'BBPS_PATH',          plugin_dir_path( __FILE__ ) );
	define( 'BBPS_ADMIN_PATH',    BBPS_PATH .'admin/' );
	define( 'BBPS_TEMPLATE_PATH', BBPS_PATH .'templates/' );
	define( 'BBPS_INCLUDES_PATH', BBPS_PATH .'includes/' );
	define( 'BBPS_WIDGETS_PATH',  BBPS_PATH .'widgets/' );
	define( 'BBPS_URL',           plugin_dir_url( dirname( __FILE__ ) ) . basename( dirname( __FILE__ ) ) . '/' );
	define( 'BBPS_WIDGETS_URL',   plugins_url( 'widgets', __FILE__ ) );
}

/**
 * Includes all the files required for the plugin to run
 */
function edd_bbp_d_includes() {
	// Load backend fles
	if ( is_admin() ) {
		$admin_files = array(
			'admin-functions',
			'settings',
		);

		foreach ( $admin_files as $file ) {
			include BBPS_ADMIN_PATH . $file . '.php';
		}
	}

	$include_files = array(
		'actions',
		'common-functions',
		'support-functions',
		'core-options',
		'user-ranking-functions',
		'shortcodes',
		'widget-hours'
	);

	foreach ( $include_files as $file ) {
		include_once BBPS_INCLUDES_PATH . $file . '.php';
	}
}

/**
 * Register any widgets
 */
function edd_bbp_d_register_widgets() {
	register_widget( 'BBPS_Forum_Support_Hours' );
}
add_action( 'widgets_init', 'edd_bbp_d_register_widgets' );