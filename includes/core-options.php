<?php
/**
 * Core Options
 */

function bbps_add_options() {
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
add_action( 'bbps-activation', 'bbps_add_options' );

function bbps_is_post_count_enabled(){
	return get_option( '_bbps_enable_post_count' );
}

function bbps_is_user_rank_enabled(){
	return get_option( '_bbps_enable_user_rank' );
}

function bbps_is_resolved_enabled(){
	$options = get_option( '_bbps_used_status' );
	return $options['res'];
}

function bbps_is_not_resolved_enabled(){
	$options = get_option( '_bbps_used_status' );
	return $options['notres'];
}

function bbps_is_not_support_enabled(){
	$options = get_option( '_bbps_used_status' );
	return $options['notsup'];
}

function bbps_is_moderator_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return $options['mod'];
}

function bbps_is_admin_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return $options['admin'];
}

function bbps_is_user_enabled(){
	$options = get_option( '_bbps_status_permissions' );
	return $options['user'];
}

function bbps_is_topic_move_enabled(){
	return get_option( '_bbps_enable_topic_move' );
}

function bbps_is_topic_urgent_enabled(){
	return get_option( '_bbps_status_permissions_urgent' );
}

function bbps_is_topic_claim_enabled(){
	return get_option( '_bbps_claim_topic' );
}

function bbps_is_topic_claim_display_enabled(){
	return get_option( '_bbps_claim_topic_display' );
}

function bbps_is_topic_assign_enabled(){
	return get_option( '_bbps_topic_assign' );
}

function bbps_is_user_trusted_enabled(){
	return get_option( '_bbps_enable_trusted_tag' );
}