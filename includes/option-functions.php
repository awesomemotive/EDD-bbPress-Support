<?php
/**
 * Options Functions
 *
 * @package		EDD\BBP\OptionsFunctions
 * @since		2.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Is resolved enabled?
 *
 * @since		1.0.0
 * @return		bool True if enabled, false otherwise
 */
function edd_bbp_is_resolved_enabled() {
	$options = get_option( '_bbps_used_status' );
	return isset( $options['res'] ) ? $options['res'] : false;
}


/**
 * Is not resolved enabled?
 *
 * @since		1.0.0
 * @return		bool True if enabled, false otherwise
 */
function edd_bbp_is_not_resolved_enabled() {
	$options = get_option( '_bbps_used_status' );
	return isset( $options['notres'] ) ? $options['notres'] : false;
}


/**
 * Is not support enabled?
 *
 * @since		1.0.0
 * @return		bool True if enabled, false otherwise
 */
function edd_bbp_is_not_support_enabled() {
	$options = get_option( '_bbps_used_status' );
	return isset( $options['notsup'] ) ? $options['notsup'] : false;
}


/**
 * Is moderator enabled?
 *
 * @since		1.0.0
 * @return		bool True if enabled, false otherwise
 */
function edd_bbp_is_moderator_enabled() {
	$options = get_option( '_bbps_status_permissions' );
	return isset( $options['mod'] ) ? $options['mod'] : false;
}


/**
 * Is admin enabled?
 *
 * @since		1.0.0
 * @return		bool True if enabled, false otherwise
 */
function edd_bbp_is_admin_enabled() {
	$options = get_option( '_bbps_status_permissions' );
	return isset( $options['admin'] ) ? $options['admin'] : false;
}


/**
 * Is user enabled?
 *
 * @since		1.0.0
 * @return		bool True if enabled, false otherwise
 */
function edd_bbp_is_user_enabled() {
	$options = get_option( '_bbps_status_permissions' );
	return isset( $options['user'] ) ? $options['user'] : false;
}


/**
 * Is topic assigned enabled?
 *
 * @since		1.0.0
 * @return		bool True if enabled, false otherwise
 */
function edd_bbp_is_topic_assign_enabled() {
	return get_option( '_bbps_topic_assign' );
}
