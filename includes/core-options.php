<?php
/**
 * Core Options
 */

function edd_bbp_d_add_options() {
	// Default options
	$options = array (
		'_bbps_default_status'            => '1',
		'_bbps_used_status'               => '',
		'_bbps_enable_topic_move'         => '1',
		'_bbps_status_permissions_urgent' => '',
	);

	// Add default options
	foreach ( $options as $key => $value )
		add_option( $key, $value );
}
add_action( 'edd_bbp_d_activation', 'edd_bbp_d_add_options' );

function edd_bbp_d_is_resolved_enabled(){
	$options = get_option( '_bbps_used_status' );
	return isset( $options['res'] ) ? $options['res'] : false;
}

function edd_bbp_d_is_not_resolved_enabled(){
	$options = get_option( '_bbps_used_status' );
	return isset( $options['notres'] ) ? $options['notres'] : false;
}

function edd_bbp_d_is_not_support_enabled(){
	$options = get_option( '_bbps_used_status' );
	return isset( $options['notsup'] ) ? $options['notsup'] : false;
}

function edd_bbp_d_is_moderator_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return isset( $options['mod'] ) ? $options['mod'] : false;
}

function edd_bbp_d_is_admin_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return isset( $options['admin'] ) ? $options['admin'] : false;
}

function edd_bbp_d_is_user_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return isset( $options['user'] ) ? $options['user'] : false;
}


function edd_bbp_d_is_topic_assign_enabled(){
	return get_option( '_bbps_topic_assign' );
}