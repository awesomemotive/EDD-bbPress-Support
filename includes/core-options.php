<?php
/**
 * Core Options
 */

function edd_bbp_d_add_options() {
	// Default options
	$options = array (
		'_bbps_default_status'            => '1',
		'_bbps_enable_post_count'         => '1',
		'_bbps_enable_user_rank'          => '1',
		'_bbps_status_permissions'        => '',
		'_bbps_reply_count'               => '',
		'_bbps_used_status'               => '',
		'_bbps_enable_topic_move'         => '1',
		'_bbps_status_permissions_urgent' => '',
	);

	// Add default options
	foreach ( $options as $key => $value )
		add_option( $key, $value );
}
add_action( 'edd_bbp_d_activation', 'edd_bbp_d_add_options' );

function edd_bbp_d_is_post_count_enabled(){
	return get_option( '_bbps_enable_post_count' );
}

function edd_bbp_d_is_user_rank_enabled(){
	return get_option( '_bbps_enable_user_rank' );
}

function edd_bbp_d_is_resolved_enabled(){
	$options = get_option( '_bbps_used_status' );
	return $options['res'];
}

function edd_bbp_d_is_not_resolved_enabled(){
	$options = get_option( '_bbps_used_status' );
	return $options['notres'];
}

function edd_bbp_d_is_not_support_enabled(){
	$options = get_option( '_bbps_used_status' );
	return $options['notsup'];
}

function edd_bbp_d_is_moderator_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return $options['mod'];
}

function edd_bbp_d_is_admin_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return $options['admin'];
}

function edd_bbp_d_is_user_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return $options['user'];
}

function edd_bbp_d_is_topic_move_enabled(){
	return get_option( '_bbps_enable_topic_move' );
}

function edd_bbp_d_is_topic_urgent_enabled(){
	return get_option( '_bbps_status_permissions_urgent' );
}

function edd_bbp_d_is_topic_claim_enabled(){
	return get_option( '_bbps_claim_topic' );
}

function edd_bbp_d_is_topic_claim_display_enabled(){
	return get_option( '_bbps_claim_topic_display' );
}

function edd_bbp_d_is_topic_assign_enabled(){
	return get_option( '_bbps_topic_assign' );
}

function edd_bbp_d_is_user_trusted_enabled(){
	return get_option( '_bbps_enable_trusted_tag' );
}